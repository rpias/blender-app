@extends('layouts.app')

@section('titulo', 'Obras')

@section('contenido')
    <div class="encabezado">
        <h1>Obras</h1>
        <a class="btn primario" href="{{ route('obras.create') }}">Nueva obra</a>
    </div>

    <form method="get" class="buscador">
        <input type="search" name="buscar" value="{{ $buscar }}" placeholder="Buscar por número, nombre o cliente">
        <button class="btn" type="submit">Buscar</button>
    </form>

    <table class="tabla">
        <thead>
        <tr>
            <th>Nº</th>
            <th>Nombre</th>
            <th>Cliente</th>
            <th>Ubicación</th>
            <th>Creada</th>
            <th>Estado</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @forelse ($obras as $obra)
            <tr>
                <td class="num">{{ $obra->numero }}</td>
                <td>{{ $obra->nombre }}</td>
                <td>{{ $obra->cliente ?: '—' }}</td>
                <td>{{ $obra->ubicacion ?: '—' }}</td>
                <td>{{ $obra->fechaCreacion?->format('d/m/Y') }}</td>
                <td><span class="chip">{{ $obra->estadoSeguimiento ?: 'ABIERTA' }}</span></td>
                <td class="acciones"><a href="{{ route('obras.show', $obra) }}">Ver</a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="vacio">Todavía no hay obras cargadas.</td></tr>
        @endforelse
        </tbody>
    </table>

    {{ $obras->links() }}
@endsection
