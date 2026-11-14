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
                <td class="num" data-etiqueta="Nº">{{ $obra->numero }}</td>
                <td data-etiqueta="Nombre">{{ $obra->nombre }}</td>
                <td data-etiqueta="Cliente">{{ $obra->cliente ?: '—' }}</td>
                <td data-etiqueta="Ubicación">{{ $obra->ubicacion ?: '—' }}</td>
                <td data-etiqueta="Creada">{{ $obra->fechaCreacion?->format('d/m/Y') }}</td>
                <td data-etiqueta="Estado"><span class="chip">{{ $obra->estadoSeguimiento ?: 'ABIERTA' }}</span></td>
                <td class="acciones" data-etiqueta=""><a href="{{ route('obras.show', $obra) }}" class="btn-chico">Ver</a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="vacio">Todavía no hay obras cargadas.</td></tr>
        @endforelse
        </tbody>
    </table>

    @include('partials.paginacion', ['paginador' => $obras])
@endsection
