@extends('layouts.app')

@section('titulo', 'Panel')

@section('contenido')
    <div class="encabezado">
        <div>
            <h2 class="saludo">Hola, {{ auth()->user()->nombre ?? auth()->user()->name }}</h2>
            <p class="ayuda">Resumen de la carpintería al {{ now()->format('d/m/Y') }}.</p>
        </div>
        <a class="btn primario" href="{{ route('obras.create') }}">Nueva obra</a>
    </div>

    @if (! empty($alertas))
        <section class="alertas">
            @foreach ($alertas as $alerta)
                <article class="alerta alerta-{{ $alerta['tipo'] }}">
                    <h2>{{ $alerta['titulo'] }}</h2>
                    <ul>
                        @foreach ($alerta['items'] as $item)
                            <li>
                                <a href="{{ route('obras.show', $item['obra']) }}">
                                    {{ $item['obra']->numero }} · {{ $item['obra']->nombre }}
                                </a>
                                — {{ $item['detalle'] }}
                                @if ($item['fecha'])
                                    <span class="alerta-fecha">{{ $item['fecha'] }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </section>
    @endif

    <section class="kpis">
        <article class="kpi">
            <span class="kpi-icono azul"><svg class="icono-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="7" height="16" rx="1.5"/><rect x="14" y="4" width="7" height="10" rx="1.5"/></svg></span>
            <p class="kpi-label">Obras abiertas</p>
            <p class="kpi-valor">{{ number_format($abiertas, 0, ',', '.') }}</p>
            <p class="kpi-pie">{{ $cerradas }} cerradas · {{ $obras }} en total</p>
        </article>

        <article class="kpi">
            <span class="kpi-icono verde"><svg class="icono-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v18"/><path d="M16.5 7.5c0-1.7-2-3-4.5-3S7.5 5.8 7.5 7.5 9.5 10 12 10s4.5 1.3 4.5 3-2 3-4.5 3-4.5-1.3-4.5-3"/></svg></span>
            <p class="kpi-label">Presupuestado</p>
            <p class="kpi-valor">U$S {{ number_format($presupuestado, 0, ',', '.') }}</p>
            <p class="kpi-pie">Todas las obras</p>
        </article>

        <article class="kpi">
            <span class="kpi-icono violeta"><svg class="icono-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5.5h16"/><path d="M4 12h16"/><path d="M4 18.5h10"/></svg></span>
            <p class="kpi-label">Aberturas cargadas</p>
            <p class="kpi-valor">{{ number_format($aberturas, 0, ',', '.') }}</p>
            <p class="kpi-pie">Con precio calculado</p>
        </article>

        <article class="kpi">
            <span class="kpi-icono naranja"><svg class="icono-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="3.2"/><path d="M5 20c0-3.3 3.1-6 7-6s7 2.7 7 6"/></svg></span>
            <p class="kpi-label">Clientes</p>
            <p class="kpi-valor">{{ number_format($clientes, 0, ',', '.') }}</p>
            <p class="kpi-pie">En la cartera</p>
        </article>
    </section>


    @php
        $anchoLienzo = 760;
        $altoLienzo = 210;
        $margenIzq = 44;
        $margenDer = 14;
        $margenSup = 16;
        $margenInf = 28;
        $pasoX = count($serie) > 1 ? ($anchoLienzo - $margenIzq - $margenDer) / (count($serie) - 1) : 0;
        $tope = max(1.0, collect($serie)->max('total'));
        $altoUtil = $altoLienzo - $margenSup - $margenInf;
        $puntos = [];
        foreach ($serie as $i => $mes) {
            $puntos[] = [
                'x' => round($margenIzq + $i * $pasoX, 1),
                'y' => round($margenSup + $altoUtil - ($mes['total'] / $tope) * $altoUtil, 1),
                'etiqueta' => $mes['etiqueta'],
                'total' => $mes['total'],
            ];
        }
        $linea = collect($puntos)->map(fn ($p) => $p['x'].','.$p['y'])->implode(' ');
        $area = $margenIzq.','.($altoLienzo - $margenInf).' '.$linea.' '.end($puntos)['x'].','.($altoLienzo - $margenInf);
    @endphp

    <section class="tarjeta grafico-mensual">
        <div class="tarjeta-cabecera">
            <h2>Presupuestado por mes</h2>
            <span class="ayuda">Últimos 6 meses</span>
        </div>

        <svg viewBox="0 0 {{ $anchoLienzo }} {{ $altoLienzo }}" role="img"
             aria-label="Presupuesto por mes de los últimos 6 meses" preserveAspectRatio="none">
            @foreach ([0, 0.5, 1] as $franja)
                @php($y = $margenSup + $altoUtil - $franja * $altoUtil)
                <line x1="{{ $margenIzq }}" y1="{{ $y }}" x2="{{ $anchoLienzo - $margenDer }}" y2="{{ $y }}"
                      class="grafico-grid"/>
                <text x="{{ $margenIzq - 8 }}" y="{{ $y + 4 }}" class="grafico-eje" text-anchor="end">
                    {{ number_format($tope * $franja, 0, ',', '.') }}
                </text>
            @endforeach

            <polygon points="{{ $area }}" class="grafico-area"/>
            <polyline points="{{ $linea }}" class="grafico-linea"/>

            @foreach ($puntos as $punto)
                <circle cx="{{ $punto['x'] }}" cy="{{ $punto['y'] }}" r="4" class="grafico-punto">
                    <title>{{ $punto['etiqueta'] }}: U$S {{ number_format($punto['total'], 0, ',', '.') }}</title>
                </circle>
                <text x="{{ $punto['x'] }}" y="{{ $altoLienzo - 8 }}" class="grafico-etiqueta" text-anchor="middle">
                    {{ $punto['etiqueta'] }}
                </text>
            @endforeach
        </svg>
    </section>

    <div class="dos-columnas">
        <section class="tarjeta">
            <div class="tarjeta-cabecera">
                <h2>Últimas obras</h2>
                <a class="link" href="{{ route('obras.index') }}">Ver todas</a>
            </div>

            <table class="tabla">
                <thead>
                <tr>
                    <th>Nº</th>
                    <th>Obra</th>
                    <th>Estado</th>
                    <th class="num">Total</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($ultimas as $fila)
                    <tr>
                        <td>{{ $fila['obra']->numero }}</td>
                        <td>
                            <a class="link" href="{{ route('obras.show', $fila['obra']) }}">{{ $fila['obra']->nombre }}</a>
                        </td>
                        <td>
                            <span class="chip {{ $fila['obra']->estadoSeguimiento === 'CERRADA' ? 'chip-gris' : 'chip-verde' }}">
                                {{ $fila['obra']->estadoSeguimiento === 'CERRADA' ? 'Cerrada' : 'Abierta' }}
                            </span>
                        </td>
                        <td class="num">{{ number_format($fila['total'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="vacio">Todavía no hay obras cargadas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </section>

        <section class="tarjeta">
            <div class="tarjeta-cabecera">
                <h2>Tipologías más usadas</h2>
            </div>

            @php($maximo = $tipologias->max('cantidad') ?: 1)
            @forelse ($tipologias as $tipologia)
                <div class="barra-fila">
                    <div class="barra-texto">
                        <span>{{ $tipologia->nombre }}</span>
                        <strong>{{ $tipologia->cantidad }}</strong>
                    </div>
                    <div class="barra-pista">
                        <div class="barra-valor" style="width: {{ round($tipologia->cantidad / $maximo * 100) }}%"></div>
                    </div>
                </div>
            @empty
                <p class="vacio">Sin aberturas cargadas.</p>
            @endforelse
        </section>
    </div>
@endsection
