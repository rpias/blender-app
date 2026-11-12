@extends('layouts.app')

@section('titulo', $config->titulo)

@section('contenido')
    <div class="encabezado">
        <div>
            <h1>Catálogos</h1>
            <p class="ayuda">
                Los cambios se aplican a los presupuestos nuevos. Los presupuestos ya generados
                conservan su precio calculado.
            </p>
        </div>
    </div>

    <nav class="tabs">
        @foreach ($todos as $clave => $item)
            <a href="{{ route('catalogos.index', $clave) }}"
               class="{{ $clave === $recurso ? 'activo' : '' }}">{{ $item->titulo }}</a>
        @endforeach
    </nav>

    <div class="encabezado">
        <h2>{{ $config->titulo }}</h2>
        @if ($config->busqueda !== [])
            <form method="get" class="buscador chico">
                <input type="search" name="buscar" value="{{ $buscar }}" placeholder="Buscar">
                <button class="btn" type="submit">Buscar</button>
            </form>
        @endif
    </div>

    <form method="post" action="{{ route('catalogos.update', $recurso) }}">
        @csrf

        <table class="tabla tabla-catalogo">
            <thead>
            <tr>
                @foreach ($config->columnas as $columna)
                    <th class="{{ $columna['tipo'] === 'solo-lectura' ? '' : 'editable' }}">{{ $columna['etiqueta'] }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @forelse ($filas as $fila)
                @php($identificador = $config->identificadorDe($fila))
                <tr>
                    @foreach ($config->columnas as $columna)
                        @php($campo = $columna['campo'])
                        <td class="{{ $columna['tipo'] === 'solo-lectura' ? 'lectura' : 'editable' }}">
                            @if ($columna['tipo'] === 'solo-lectura')
                                {{ $fila->{$campo} }}
                            @else
                                <input
                                    type="{{ $columna['tipo'] === 'numero' ? 'number' : ($columna['tipo'] === 'entero' ? 'number' : 'text') }}"
                                    @if ($columna['tipo'] === 'numero') step="any" @endif
                                    @if ($columna['tipo'] === 'entero') step="1" @endif
                                    name="filas[{{ $identificador }}][{{ $campo }}]"
                                    value="{{ $fila->{$campo} }}"
                                    aria-label="{{ $columna['etiqueta'] }}">
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($config->columnas) }}" class="vacio">Sin resultados.</td></tr>
            @endforelse
            </tbody>
        </table>

        <div class="acciones-form">
            <button type="submit" class="btn primario">Guardar cambios</button>
            <span class="ayuda">{{ $filas->total() }} filas</span>
        </div>
    </form>

    {{ $filas->links() }}
@endsection
