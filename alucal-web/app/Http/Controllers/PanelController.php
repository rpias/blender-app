<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Obra;
use App\Servicios\Exportacion\LineasPresupuesto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Panel de inicio con indicadores del negocio (spec 0010).
 */
class PanelController extends Controller
{
    private const MESES = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    /**
     * Totales presupuestados por mes de los últimos 6 meses.
     *
     * @param  Collection<int, Obra>  $obras
     * @param  array<int, float>  $porObra
     * @return list<array{etiqueta: string, total: float}>
     */
    private function serieMensual($obras, array $porObra): array
    {
        $meses = [];

        // Ventana: termina en el mes más reciente con datos válidos (o el actual).
        // Las obras importadas traen 0000-00-00 y no se pueden graficar.
        $fin = now()->startOfMonth();

        foreach ($obras as $obra) {
            $fecha = $obra->fechaCreacion;

            if ($fecha !== null && $fecha->year >= 1900 && $fecha->gt($fin)) {
                $fin = $fecha->startOfMonth();
            }
        }

        for ($i = 5; $i >= 0; $i--) {
            $mes = $fin->copy()->subMonths($i);
            $meses[$mes->format('Y-m')] = [
                'etiqueta' => self::MESES[$mes->month - 1],
                'total' => 0.0,
            ];
        }

        foreach ($obras as $obra) {
            $fecha = $obra->fechaCreacion;

            if ($fecha === null) {
                continue;
            }

            if ($fecha->year < 1900) {
                continue;
            }

            $clave = $fecha->format('Y-m');

            if (isset($meses[$clave])) {
                $meses[$clave]['total'] += $porObra[$obra->id] ?? 0.0;
            }
        }

        return array_values($meses);
    }

    /**
     * Alertas del panel: acciones vencidas, próximas y obras sin aberturas.
     *
     * @param  Collection<int, Obra>  $obras
     * @param  array<int, float>  $porObra
     * @return list<array{tipo: string, titulo: string, items: list<array{obra: Obra, detalle: string, fecha: string|null}>}>
     */
    private function alertas($obras, array $porObra): array
    {
        $hoy = now()->startOfDay();
        $enUnaSemana = now()->addDays(7)->endOfDay();

        $vencidas = [];
        $proximas = [];

        foreach ($obras as $obra) {
            if ($obra->estadoSeguimiento !== 'ABIERTA' || $obra->proximaAccionFecha === null) {
                continue;
            }

            $fecha = $obra->proximaAccionFecha;

            if ($fecha->lt($hoy)) {
                $vencidas[] = [
                    'obra' => $obra,
                    'detalle' => (string) ($obra->proximaAccionDetalle ?: 'Acción pendiente'),
                    'fecha' => $fecha->format('d/m/Y'),
                ];
            } elseif ($fecha->lte($enUnaSemana)) {
                $proximas[] = [
                    'obra' => $obra,
                    'detalle' => (string) ($obra->proximaAccionDetalle ?: 'Acción pendiente'),
                    'fecha' => $fecha->format('d/m/Y'),
                ];
            }
        }

        $sinAberturas = [];
        foreach ($obras as $obra) {
            if (($porObra[$obra->id] ?? 0.0) <= 0.0) {
                $sinAberturas[] = [
                    'obra' => $obra,
                    'detalle' => 'Sin aberturas con precio',
                    'fecha' => null,
                ];
            }
        }

        $alertas = [];

        if ($vencidas !== []) {
            $alertas[] = ['tipo' => 'vencida', 'titulo' => 'Próxima acción vencida', 'items' => $vencidas];
        }

        if ($proximas !== []) {
            $alertas[] = ['tipo' => 'proxima', 'titulo' => 'Próxima acción esta semana', 'items' => $proximas];
        }

        if ($sinAberturas !== []) {
            $alertas[] = ['tipo' => 'incompleta', 'titulo' => 'Obras sin aberturas cargadas', 'items' => $sinAberturas];
        }

        return $alertas;
    }

    /**
     * ¿Hay al menos una obra con fecha de creación utilizable?
     *
     * @param  Collection<int, Obra>  $obras
     */
    private function hayFechasValidas($obras): bool
    {
        foreach ($obras as $obra) {
            if ($obra->fechaCreacion !== null && $obra->fechaCreacion->year >= 1900) {
                return true;
            }
        }

        return false;
    }

    public function index(LineasPresupuesto $lineas): View
    {
        $obras = Obra::query()
            ->with(['elementos.aberturaSimple.tipologia', 'elementos.aberturaSimple.serie', 'elementos.hijos'])
            ->orderByDesc('id')
            ->get();

        $abiertas = $obras->where('estadoSeguimiento', 'ABIERTA')->count();
        $cerradas = $obras->where('estadoSeguimiento', 'CERRADA')->count();

        $presupuestado = 0.0;
        $porObra = [];

        foreach ($obras as $obra) {
            $total = $lineas->total($lineas->para($obra));
            $porObra[$obra->id] = $total;
            $presupuestado += $total;
        }

        $aberturas = DB::table('abertura_simple')
            ->whereNotNull('precioUnitario')
            ->count();

        $tipologias = DB::table('abertura_simple as a')
            ->join('tipologia as t', 't.id', '=', 'a.idTipologia')
            ->selectRaw('t.nombre as nombre, count(*) as cantidad')
            ->groupBy('t.id', 't.nombre')
            ->orderByDesc('cantidad')
            ->limit(5)
            ->get();

        $ultimas = $obras->take(5)->map(fn (Obra $obra) => [
            'obra' => $obra,
            'total' => $porObra[$obra->id] ?? 0.0,
        ]);

        $alertas = $this->alertas($obras, $porObra);
        $serie = $this->serieMensual($obras, $porObra);

        return view('panel.index', [
            'abiertas' => $abiertas,
            'cerradas' => $cerradas,
            'aberturas' => $aberturas,
            'clientes' => Cliente::query()->count(),
            'presupuestado' => $presupuestado,
            'ultimas' => $ultimas,
            'tipologias' => $tipologias,
            'obras' => $obras->count(),
            'alertas' => $alertas,
            'serie' => $serie,
            'hayFechas' => $this->hayFechasValidas($obras),
        ]);
    }
}
