<?php

namespace App\Http\Controllers;

use App\Models\RegistroSuceso;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Consulta del registro de sucesos (spec 0012). Sólo lectura.
 */
class SucesoController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'usuario' => ['nullable', 'string', 'max:120'],
            'accion' => ['nullable', 'string', 'max:40'],
            'entidad' => ['nullable', 'string', 'max:60'],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date'],
            'buscar' => ['nullable', 'string', 'max:120'],
        ]);

        $sucesos = RegistroSuceso::query()
            ->when($filtros['usuario'] ?? null, fn ($q, $usuario) => $q->where('usuario_nombre', 'like', "%{$usuario}%"))
            ->when($filtros['accion'] ?? null, fn ($q, $accion) => $q->where('accion', $accion))
            ->when($filtros['entidad'] ?? null, fn ($q, $entidad) => $q->where('entidad', $entidad))
            ->when($filtros['desde'] ?? null, fn ($q, $desde) => $q->whereDate('created_at', '>=', $desde))
            ->when($filtros['hasta'] ?? null, fn ($q, $hasta) => $q->whereDate('created_at', '<=', $hasta))
            ->when($filtros['buscar'] ?? null, fn ($q, $texto) => $q->where('descripcion', 'like', "%{$texto}%"))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('registro.index', [
            'sucesos' => $sucesos,
            'filtros' => $filtros,
            'acciones' => RegistroSuceso::query()->distinct()->orderBy('accion')->pluck('accion'),
            'entidades' => RegistroSuceso::query()->whereNotNull('entidad')->distinct()->orderBy('entidad')->pluck('entidad'),
        ]);
    }
}
