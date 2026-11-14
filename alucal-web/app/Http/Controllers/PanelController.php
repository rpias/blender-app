<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Obra;
use App\Servicios\Exportacion\LineasPresupuesto;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Panel de inicio con indicadores del negocio (spec 0010).
 */
class PanelController extends Controller
{
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

        return view('panel.index', [
            'abiertas' => $abiertas,
            'cerradas' => $cerradas,
            'aberturas' => $aberturas,
            'clientes' => Cliente::query()->count(),
            'presupuestado' => $presupuestado,
            'ultimas' => $ultimas,
            'tipologias' => $tipologias,
            'obras' => $obras->count(),
        ]);
    }
}
