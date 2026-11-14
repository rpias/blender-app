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
                <td class="num" data-etiqueta="Nº">{{ $cliente->numero }}</td>
                <td data-etiqueta="Nombre">{{ $cliente->nombre }}</td>
                <td data-etiqueta="Empresa">{{ $cliente->empresa ?: '—' }}</td>
                <td data-etiqueta="Teléfono">{{ $cliente->telefono ?: '—' }}</td>
                <td data-etiqueta="Correo">{{ $cliente->email ?: '—' }}</td>
                <td data-etiqueta="Dirección">{{ $cliente->direccion ?: '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="vacio">No hay clientes cargados.</td></tr>
        @endforelse
        </tbody>
    </table>

    @include('partials.paginacion', ['paginador' => $clientes])
@endsection
