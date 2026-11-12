<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar', ''));

        $clientes = Cliente::query()
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('empresa', 'like', "%{$buscar}%")
                    ->orWhere('email', 'like', "%{$buscar}%");
            })
            ->orderBy('nombre')
            ->paginate(25)
            ->withQueryString();

        return view('clientes.index', compact('clientes', 'buscar'));
    }

    public function create(): View
    {
        return view('clientes.create', [
            'proximoNumero' => (int) (Cliente::max('numero') ?? 0) + 1,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:160'],
            'empresa' => ['nullable', 'string', 'max:160'],
            'telefono' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:120'],
            'direccion' => ['nullable', 'string', 'max:160'],
            'alias' => ['nullable', 'string', 'max:60'],
        ]);

        $datos['numero'] = (int) (Cliente::max('numero') ?? 0) + 1;

        Cliente::create($datos);

        return redirect()
            ->route('clientes.index')
            ->with('estado', 'Cliente creado correctamente.');
    }
}
