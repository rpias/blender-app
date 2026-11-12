@extends('layouts.app')

@section('titulo', 'Clientes')

@section('contenido')
    <div class="encabezado">
        <h1>Clientes</h1>
        <a class="btn primario" href="{{ route('clientes.create') }}">Nuevo cliente</a>
    </div>

    <form method="get" class="buscador">
        <input type="search" name="buscar" value="{{ $buscar }}" placeholder="Buscar por nombre, empresa o correo">
        <button class="btn" type="submit">Buscar</button>
    </form>

    <table class="tabla">
        <thead>
        <tr>
            <th>Nº</th>
            <th>Nombre</th>
            <th>Empresa</th>
            <th>Teléfono</th>
            <th>Correo</th>
            <th>Dirección</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($clientes as $cliente)
            <tr>
                <td class="num">{{ $cliente->numero }}</td>
                <td>{{ $cliente->nombre }}</td>
                <td>{{ $cliente->empresa ?: '—' }}</td>
                <td>{{ $cliente->telefono ?: '—' }}</td>
                <td>{{ $cliente->email ?: '—' }}</td>
                <td>{{ $cliente->direccion ?: '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="vacio">No hay clientes cargados.</td></tr>
        @endforelse
        </tbody>
    </table>

    {{ $clientes->links() }}
@endsection
