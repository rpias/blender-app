@extends('layouts.app')

@section('titulo', 'Usuarios')

@section('contenido')
    <div class="encabezado">
        <div>
            <h1>Usuarios</h1>
            <p class="ayuda">
                Los administradores editan catálogos y usuarios; los vendedores cargan obras y presupuestos.
            </p>
        </div>
    </div>

    <form method="post" action="{{ route('usuarios.store') }}" class="tarjeta form-alta">
        @csrf
        <h3>Agregar usuario</h3>
        <div class="grilla">
            <label>
                Nombre
                <input type="text" name="nombre" value="{{ old('nombre') }}" maxlength="120" required>
            </label>
            <label>
                Correo
                <input type="email" name="email" value="{{ old('email') }}" maxlength="190" required>
            </label>
            <label>
                Contraseña inicial
                <input type="password" name="password" minlength="8" required>
            </label>
            <label>
                Rol
                <select name="rol">
                    <option value="vendedor">Vendedor</option>
                    <option value="admin">Administrador</option>
                </select>
            </label>
        </div>
        <button type="submit" class="btn primario">Crear usuario</button>
    </form>

    <table class="tabla">
        <thead>
        <tr>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Rol y estado</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($usuarios as $usuario)
            <tr>
                <td>{{ $usuario->nombre ?: $usuario->name }}</td>
                <td>{{ $usuario->email }}</td>
                <td>
                    <form method="post" action="{{ route('usuarios.update', $usuario) }}" class="form-fila">
                        @csrf
                        @method('PATCH')
                        <select name="rol">
                            <option value="vendedor" @selected($usuario->rol === 'vendedor')>Vendedor</option>
                            <option value="admin" @selected($usuario->rol === 'admin')>Administrador</option>
                        </select>
                        <label class="casilla">
                            <input type="checkbox" name="activo" value="1" @checked($usuario->activo)> Activo
                        </label>
                        <button type="submit" class="btn">Guardar</button>
                    </form>
                </td>
                <td class="acciones-fila">
                    @if ($usuario->id !== auth()->id())
                        <form method="post" action="{{ route('usuarios.destroy', $usuario) }}"
                              onsubmit="return confirm('¿Eliminar el usuario?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="link-boton">Eliminar</button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
