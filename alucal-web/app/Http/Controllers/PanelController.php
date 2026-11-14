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
        ]);
    }
}
