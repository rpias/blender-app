<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestión de usuarios (spec 0008): sólo administradores.
 */
class UsuarioController extends Controller
{
    public function index(): View
    {
        return view('usuarios.index', [
            'usuarios' => User::query()->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'rol' => ['required', 'string', 'in:admin,vendedor'],
        ]);

        User::query()->create([
            'name' => $datos['nombre'],
            'nombre' => $datos['nombre'],
            'email' => $datos['email'],
            'password' => $datos['password'],
            'rol' => $datos['rol'],
            'activo' => true,
        ]);

        return back()->with('estado', 'Usuario creado.');
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $datos = $request->validate([
            'rol' => ['required', 'string', 'in:admin,vendedor'],
            'activo' => ['nullable', 'boolean'],
        ]);

        if ($usuario->id === $request->user()->id && $datos['rol'] !== 'admin') {
            return back()->withErrors(['rol' => 'No podés quitarte el rol de administrador.']);
        }

        $usuario->update([
            'rol' => $datos['rol'],
            'activo' => (bool) ($datos['activo'] ?? false),
        ]);

        return back()->with('estado', 'Usuario actualizado.');
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->id === $request->user()->id) {
            return back()->withErrors(['usuario' => 'No podés eliminar tu propio usuario.']);
        }

        $usuario->delete();

        return back()->with('estado', 'Usuario eliminado.');
    }
}
