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
