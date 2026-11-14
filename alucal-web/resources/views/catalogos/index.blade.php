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

    @if ($config->permiteCrear())
        <form method="post" action="{{ route('catalogos.store', $recurso) }}" class="tarjeta form-alta">
            @csrf
            <h3>Agregar fila</h3>
            <div class="grilla">
                @foreach ($config->camposCrear as $campo)
                    <label>
                        {{ $campo['etiqueta'] }}
                        <input
                            type="{{ $campo['tipo'] === 'texto' ? 'text' : 'number' }}"
                            @if (in_array($campo['tipo'], ['numero', 'moneda', 'decimal'], true)) step="any" @endif
                            @if ($campo['tipo'] === 'entero') step="1" @endif
                            name="{{ $campo['campo'] }}"
                            value="{{ old($campo['campo']) }}"
                            @if ($campo['tipo'] === 'moneda') placeholder="Pesos, ej. 25,50" @endif
                            required>
                    </label>
                @endforeach
            </div>
            <button type="submit" class="btn primario">Agregar</button>
        </form>
    @endif

    <form method="post" action="{{ route('catalogos.update', $recurso) }}">
        @csrf

        <table class="tabla tabla-catalogo">
            <thead>
            <tr>
                @foreach ($config->columnas as $columna)
                    <th class="{{ $columna['tipo'] === 'solo-lectura' ? '' : 'editable' }}">{{ $columna['etiqueta'] }}</th>
                @endforeach
                @if ($config->permiteEliminar)
                    <th></th>
                @endif
            </tr>
            </thead>
            <tbody>
            @forelse ($filas as $fila)
                @php($identificador = $config->identificadorDe($fila))
                <tr>
                    @foreach ($config->columnas as $columna)
                        @php($campo = $columna['campo'])
                        <td class="{{ $columna['tipo'] === 'solo-lectura' ? 'lectura' : 'editable' }}"
                            data-etiqueta="{{ $columna['etiqueta'] }}">
                            @if ($columna['tipo'] === 'solo-lectura')
                                {{ $fila->{$campo} }}
                            @else
                                @php($esMoneda = $columna['tipo'] === 'moneda')
                                @php($esDecimal = $columna['tipo'] === 'decimal')
                                @php($esNumero = in_array($columna['tipo'], ['numero', 'entero', 'moneda', 'decimal'], true))
                                @php($valorMostrado = $esMoneda ? ((float) $fila->{$campo} / 100) : $fila->{$campo})
                                <input
                                    type="{{ $esNumero ? 'number' : 'text' }}"
                                    @if ($columna['tipo'] === 'numero') step="any" @endif
                                    @if ($columna['tipo'] === 'entero') step="1" @endif
                                    @if ($esMoneda || $esDecimal) step="0.01" @endif
                                    name="filas[{{ $identificador }}][{{ $campo }}]"
                                    value="{{ ($esMoneda || $esDecimal) ? number_format((float) $valorMostrado, 2, '.', '') : $valorMostrado }}"
                                    aria-label="{{ $columna['etiqueta'] }}"
                                    @if ($esMoneda || $esDecimal) title="{{ \App\Support\Moneda::conSimbolo($esMoneda ? $fila->{$campo} / 100 : $fila->{$campo}) }}" @endif>
                            @endif
                        </td>
                    @endforeach
                    @if ($config->permiteEliminar)
                        <td class="acciones-fila" data-etiqueta="">
                            <form method="post" action="{{ route('catalogos.destroy', [$recurso, $identificador]) }}"
                                  onsubmit="return confirm('¿Eliminar esta fila del catálogo?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="link-boton">Eliminar</button>
                            </form>
                        </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="{{ count($config->columnas) + ($config->permiteEliminar ? 1 : 0) }}" class="vacio">Sin resultados.</td></tr>
            @endforelse
            </tbody>
        </table>

        <div class="acciones-form">
            <button type="submit" class="btn primario">Guardar cambios</button>
            <span class="ayuda">{{ $filas->total() }} filas</span>
        </div>
    </form>

    @include('partials.paginacion', ['paginador' => $filas])
@endsection
