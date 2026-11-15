@extends('layouts.app')

@section('titulo', 'Cómputo · Obra '.$obra->numero)

@section('contenido')
    <div class="encabezado">
        <div>
            <h2 class="saludo">Cómputo de materiales</h2>
            <p class="ayuda">Obra {{ $obra->numero }} · {{ $obra->nombre }} · {{ $computo['aberturas'] }} aberturas calculadas.</p>
        </div>
        <div class="acciones-encabezado">
            <a href="{{ route('obras.computo.xlsx', $obra) }}" class="btn primario">Exportar Excel</a>
            <a href="{{ route('obras.show', $obra) }}" class="link">Volver a la obra</a>
        </div>
    </div>

    <div class="metricas">
        <div class="metrica"><span>{{ count($computo['perfiles']) }}</span>Cortes distintos</div>
        <div class="metrica"><span>{{ number_format($computo['kilos'], 2, ',', '.') }}</span>Kilos de perfil</div>
        <div class="metrica"><span>{{ count($computo['accesorios']) }}</span>Accesorios</div>
        <div class="metrica"><span>{{ count($computo['vidrios']) }}</span>Vidrios</div>
    </div>

    <section class="tarjeta">
        <h2>Lista de corte · perfiles</h2>

        <table class="tabla">
            <thead>
            <tr>
                <th>Perfil</th>
                <th>Descripción</th>
                <th class="num">Corte (mm)</th>
                <th class="num">Cantidad</th>
                <th class="num">Metros</th>
                <th class="num">Kilos</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($computo['perfiles'] as $fila)
                <tr>
                    <td data-etiqueta="Perfil">{{ $fila['codigo'] }}</td>
                    <td data-etiqueta="Descripción">{{ $fila['descripcion'] ?: '—' }}</td>
                    <td class="num" data-etiqueta="Corte (mm)">{{ number_format($fila['largoMM'], 0, ',', '.') }}</td>
                    <td class="num" data-etiqueta="Cantidad">{{ $fila['cantidad'] }}</td>
                    <td class="num" data-etiqueta="Metros">{{ \App\Support\Moneda::formatear($fila['metros']) }}</td>
                    <td class="num" data-etiqueta="Kilos">{{ \App\Support\Moneda::formatear($fila['kilos']) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="vacio">La obra no tiene perfiles calculados.</td></tr>
            @endforelse
            </tbody>
            @if ($computo['perfiles'] !== [])
                <tfoot>
                <tr>
                    <th colspan="4">Total</th>
                    <th class="num">{{ \App\Support\Moneda::formatear(collect($computo['perfiles'])->sum('metros')) }}</th>
                    <th class="num">{{ \App\Support\Moneda::formatear($computo['kilos']) }}</th>
                </tr>
                </tfoot>
            @endif
        </table>
    </section>


    <section class="tarjeta">
        <div class="tarjeta-cabecera">
            <h2>Optimización de corte</h2>
            <form method="get" class="form-fila">
                <label class="casilla">
                    Barra (mm)
                    <input type="number" name="barra" value="{{ $largoBarra }}" min="1000" max="12000" step="100">
                </label>
                <label class="casilla">
                    Sierra (mm)
                    <input type="number" name="kerf" value="{{ $kerf }}" min="0" max="20" step="1">
                </label>
                <button type="submit" class="btn">Recalcular</button>
            </form>
        </div>

        <p class="ayuda">
            Reparto de los tramos en barras de {{ number_format($largoBarra, 0, ',', '.') }} mm
            descontando {{ $kerf }} mm por corte.
        </p>

        <table class="tabla">
            <thead>
            <tr>
                <th>Perfil</th>
                <th class="num">Barras</th>
                <th>Cortes por barra</th>
                <th class="num">Retazo</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($optimizacion as $fila)
                <tr>
                    <td data-etiqueta="Perfil">{{ $fila['codigo'] }}</td>
                    <td class="num" data-etiqueta="Barras">{{ $fila['totalBarras'] }}</td>
                    <td data-etiqueta="Cortes por barra">
                        @foreach ($fila['barras'] as $indice => $barra)
                            <div class="barra-corte">
                                <span class="chip">#{{ $indice + 1 }}</span>
                                @foreach ($barra['cortes'] as $corte)
                                    <span class="corte">{{ number_format($corte, 0, ',', '.') }}</span>
                                @endforeach
                                <span class="sobra">retazo {{ number_format($barra['sobranteMM'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                        @if ($fila['noEntran'] !== [])
                            <p class="aviso error">
                                No entran en la barra:
                                @foreach ($fila['noEntran'] as $noEntra)
                                    {{ number_format($noEntra['largoMM'], 0, ',', '.') }} mm × {{ $noEntra['cantidad'] }}
                                @endforeach
                            </p>
                        @endif
                    </td>
                    <td class="num" data-etiqueta="Retazo">
                        {{ number_format($fila['sobranteMM'], 0, ',', '.') }} mm
                        <small>({{ $fila['sobrantePct'] }}%)</small>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="vacio">La obra no tiene cortes para optimizar.</td></tr>
            @endforelse
            </tbody>
            @if ($optimizacion !== [])
                <tfoot>
                <tr>
                    <th>Total</th>
                    <th class="num">{{ collect($optimizacion)->sum('totalBarras') }}</th>
                    <th>barras de {{ number_format($largoBarra, 0, ',', '.') }} mm</th>
                    <th class="num">{{ number_format(collect($optimizacion)->sum('sobranteMM'), 0, ',', '.') }} mm</th>
                </tr>
                </tfoot>
            @endif
        </table>
    </section>

    <div class="dos-columnas">
        <section class="tarjeta">
            <h2>Accesorios</h2>
            <table class="tabla">
                <thead>
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th class="num">Cantidad</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($computo['accesorios'] as $fila)
                    <tr>
                        <td data-etiqueta="Código">{{ $fila['codigo'] }}</td>
                        <td data-etiqueta="Descripción">{{ $fila['descripcion'] ?: '—' }}</td>
                        <td class="num" data-etiqueta="Cantidad">{{ rtrim(rtrim(number_format($fila['cantidad'], 2, ',', '.'), '0'), ',') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="vacio">Sin accesorios.</td></tr>
                @endforelse
                </tbody>
            </table>
        </section>

        <section class="tarjeta">
            <h2>Vidrios</h2>
            <table class="tabla">
                <thead>
                <tr>
                    <th>Vidrio</th>
                    <th class="num">Medida</th>
                    <th class="num">Cantidad</th>
                    <th class="num">m²</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($computo['vidrios'] as $fila)
                    <tr>
                        <td data-etiqueta="Vidrio">{{ $fila['descripcion'] }}</td>
                        <td class="num" data-etiqueta="Medida">
                            {{ number_format($fila['anchoM'] * 1000, 0, ',', '.') }} × {{ number_format($fila['altoM'] * 1000, 0, ',', '.') }}
                        </td>
                        <td class="num" data-etiqueta="Cantidad">{{ $fila['cantidad'] }}</td>
                        <td class="num" data-etiqueta="m²">{{ \App\Support\Moneda::formatear($fila['m2']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="vacio">Sin vidrios.</td></tr>
                @endforelse
                </tbody>
                @if ($computo['vidrios'] !== [])
                    <tfoot>
                    <tr>
                        <th colspan="3">Total</th>
                        <th class="num">{{ \App\Support\Moneda::formatear(collect($computo['vidrios'])->sum('m2')) }}</th>
                    </tr>
                    </tfoot>
                @endif
            </table>
        </section>
    </div>
@endsection
