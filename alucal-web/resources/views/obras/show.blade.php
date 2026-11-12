@extends('layouts.app')

@section('titulo', 'Obra '.$obra->numero)

@section('contenido')
    <div class="encabezado">
        <div>
            <h1>Obra {{ $obra->numero }} · {{ $obra->nombre }}</h1>
            <p class="ayuda">
                {{ $obra->cliente ?: 'Sin cliente' }}
                @if ($obra->ubicacion) · {{ $obra->ubicacion }} @endif
                @if ($obra->arquitecto) · Arq. {{ $obra->arquitecto }} @endif
            </p>
        </div>
        <div class="acciones-encabezado">
            <a href="{{ route('obras.pdf', $obra) }}" class="btn" target="_blank">Presupuesto PDF</a>
            <a href="{{ route('obras.xlsx', $obra) }}" class="btn">Exportar Excel</a>
            <a href="{{ route('obras.csv', $obra) }}" class="btn">Exportar CSV</a>
            <a href="{{ route('obras.edit', $obra) }}" class="btn">Editar</a>
            <a href="{{ route('obras.index') }}" class="link">Volver</a>
        </div>
    </div>

    <div class="metricas">
        <div class="metrica"><span>{{ $obra->elementos->count() }}</span>Elementos</div>
        <div class="metrica"><span>{{ $obra->elementos->sum(fn ($e) => $e->aberturaSimple->cantidad ?? 0) }}</span>Aberturas</div>
        <div class="metrica"><span>U$S {{ number_format($total, 0, ',', '.') }}</span>Total presupuestado</div>
        <div class="metrica"><span>{{ $obra->estadoSeguimiento ?: 'ABIERTA' }}</span>Estado</div>
    </div>

    <table class="tabla">
        <thead>
        <tr>
            <th>Nº</th>
            <th>Tipología</th>
            <th>Serie</th>
            <th>Medidas</th>
            <th>Cant.</th>
            <th>Ubicación</th>
            <th class="num">Unitario</th>
            <th class="num">Subtotal</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @forelse ($obra->elementos as $elemento)
            @php($ab = $elemento->aberturaSimple)
            <tr>
                <td class="num">{{ $elemento->numero }}</td>
                <td>{{ $ab?->tipologia?->nombre ?? '—' }}</td>
                <td>{{ $ab?->serie?->nombre ?? '—' }}</td>
                <td>{{ $ab ? number_format($ab->ancho, 0, ',', '.').' × '.number_format($ab->alto, 0, ',', '.') : '—' }}</td>
                <td class="num">{{ $ab?->cantidad ?? '—' }}</td>
                <td>{{ $ab?->ubicacion ?: '—' }}</td>
                <td class="num">{{ $ab?->precioUnitario !== null ? number_format((float) $ab->precioUnitario, 0, ',', '.') : '—' }}</td>
                <td class="num">{{ $ab?->precioUnitario !== null ? number_format((float) $ab->precioUnitario * (int) $ab->cantidad, 0, ',', '.') : '—' }}</td>
                <td class="acciones">
                    @if ($ab)
                        <form method="post" action="{{ route('aberturas.destroy', $ab) }}" onsubmit="return confirm('¿Eliminar la abertura?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="link-boton">Eliminar</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="9" class="vacio">Esta obra no tiene elementos todavía.</td></tr>
        @endforelse
        </tbody>
        @if ($obra->elementos->isNotEmpty())
            <tfoot>
            <tr>
                <th colspan="7">Total</th>
                <th class="num">U$S {{ number_format($total, 0, ',', '.') }}</th>
                <th></th>
            </tr>
            </tfoot>
        @endif
    </table>

    <section class="tarjeta bloque-form">
        <h2>Agregar abertura · Marco fijo</h2>
        <p class="ayuda">
            El precio se calcula con el motor de margen operativo: kilos de perfil por coeficiente de
            desperdicio y precio por kilo, más accesorios, vidrio, premarco y tapajuntas.
        </p>

        <form method="post" action="{{ route('aberturas.store', $obra) }}" id="form-abertura">
            @csrf
            <input type="hidden" name="tipo" value="ABERTURA_SIMPLE">
            <input type="hidden" name="idTipologia" value="6">

            <div class="grilla">
                <label>
                    Serie
                    <select name="idSerie" id="serie">
                        @foreach ($series as $serie)
                            <option value="{{ $serie['id'] }}" @selected(old('idSerie') == $serie['id'])>{{ $serie['nombre'] }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Ancho (mm)
                    <input type="number" name="ancho" value="{{ old('ancho', 1000) }}" min="100" max="10000" required>
                </label>

                <label>
                    Alto (mm)
                    <input type="number" name="alto" value="{{ old('alto', 1000) }}" min="100" max="10000" required>
                </label>

                <label>
                    Cantidad
                    <input type="number" name="cantidad" value="{{ old('cantidad', 1) }}" min="1" max="999" required>
                </label>

                <label>
                    Perfil de marco
                    <select name="perfilMarco" id="perfilMarco">
                        @foreach ($opcionesIniciales['perfiles'] as $perfil)
                            <option value="{{ $perfil['codigo'] }}">{{ $perfil['codigo'] }} · {{ $perfil['descripcion'] }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Contravidrio
                    <select name="perfilContravidrio" id="perfilContravidrio">
                        <option value="">Sin contravidrio</option>
                        @foreach ($opcionesIniciales['contravidrios'] as $contravidrio)
                            <option value="{{ $contravidrio }}">{{ $contravidrio }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Divisor
                    <select name="perfilDivisor" id="perfilDivisor">
                        <option value="">Sin divisor</option>
                        @foreach ($opcionesIniciales['divisores'] as $divisor)
                            <option value="{{ $divisor }}">{{ $divisor }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Divisores horizontales
                    <input type="number" name="divisoresHorizontales" value="{{ old('divisoresHorizontales', 0) }}" min="0" max="6">
                </label>

                <label>
                    Divisores verticales
                    <input type="number" name="divisoresVerticales" value="{{ old('divisoresVerticales', 0) }}" min="0" max="6">
                </label>

                <label>
                    Vidrio
                    <select name="vidrioId">
                        <option value="">Sin vidrio</option>
                        @foreach ($vidrios as $vidrio)
                            <option value="{{ $vidrio['id'] }}" @selected(old('vidrioId') == $vidrio['id'])>{{ $vidrio['etiqueta'] }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Ubicación
                    <input type="text" name="ubicacion" value="{{ old('ubicacion') }}" maxlength="60" placeholder="Ej.: Living">
                </label>

                <label>
                    Terminación
                    <select name="terminacion">
                        <option value="PINTADO_BLANCO">Pintado blanco</option>
                        <option value="PINTADO_NEGRO">Pintado negro</option>
                        <option value="PINTADO_BRONCE_OSCURO">Pintado bronce oscuro</option>
                        <option value="ANODIZADO_NATURAL">Anodizado natural</option>
                        <option value="ELECTROCOLOR_NEGRO">Electrocolor negro</option>
                    </select>
                </label>
            </div>

            @error('calculo')
                <p class="aviso error">{{ $message }}</p>
            @enderror

            <button type="submit" class="btn primario">Agregar y calcular</button>
        </form>
    </section>


    <section class="tarjeta bloque-form">
        <h2>Agregar abertura · Corrediza S20 (2 hojas)</h2>
        <p class="ayuda">
            Cálculo con las fórmulas de la Serie 20: marco, hojas, zócalo, enganche central,
            vidrio y accesorios por defecto.
        </p>

        <form method="post" action="{{ route('aberturas.store', $obra) }}">
            @csrf
            <input type="hidden" name="tipo" value="ABERTURA_SIMPLE">
            <input type="hidden" name="idTipologia" value="3">

            <div class="grilla">
                <label>
                    Serie
                    <select name="idSerie">
                        @foreach ($seriesCorrediza as $serie)
                            <option value="{{ $serie['id'] }}">{{ $serie['nombre'] }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Ancho (mm)
                    <input type="number" name="ancho" value="1500" min="100" max="10000" required>
                </label>

                <label>
                    Alto (mm)
                    <input type="number" name="alto" value="1000" min="100" max="10000" required>
                </label>

                <label>
                    Cantidad
                    <input type="number" name="cantidad" value="1" min="1" max="999" required>
                </label>

                <label>
                    Marco lateral
                    <select name="marcoLateral">
                        <option value="235">235 · guía postiza</option>
                        <option value="205">205</option>
                        <option value="291">291</option>
                    </select>
                </label>

                <label>
                    Marco inferior
                    <select name="marcoInferior">
                        <option value="204">204 · sin caja de agua</option>
                        <option value="236">236 · con guía de mosquitero</option>
                    </select>
                </label>

                <label>
                    Zócalo
                    <select name="zocalo">
                        <option value="2210">2210</option>
                        <option value="2211">2211</option>
                        <option value="201">201 · con rueda propia</option>
                    </select>
                </label>

                <label>
                    Cabezal
                    <select name="cabezal">
                        <option value="2210">2210</option>
                        <option value="2211">2211</option>
                        <option value="201">201</option>
                    </select>
                </label>

                <label>
                    Enganche central
                    <select name="enganche">
                        <option value="203">203</option>
                        <option value="216">216</option>
                    </select>
                </label>

                <label>
                    Caja de agua
                    <select name="cajaDeAgua">
                        <option value="0">No</option>
                        <option value="1">Sí</option>
                    </select>
                </label>

                <label>
                    Punzonado
                    <select name="punzonadoManual">
                        <option value="0">Automático</option>
                        <option value="1">Manual</option>
                    </select>
                </label>

                <label>
                    Divisor al medio
                    <select name="divisorAlMedio">
                        <option value="0">No (acristalamiento entero)</option>
                        <option value="1">Sí (dos paños por hoja)</option>
                    </select>
                </label>

                <label>
                    Vidrio
                    <select name="vidrioId">
                        <option value="">Sin vidrio</option>
                        @foreach ($vidrios as $vidrio)
                            <option value="{{ $vidrio['id'] }}">{{ $vidrio['etiqueta'] }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Ubicación
                    <input type="text" name="ubicacion" maxlength="60" placeholder="Ej.: Dormitorio">
                </label>

                <label>
                    Terminación
                    <select name="terminacion">
                        <option value="PINTADO_BLANCO">Pintado blanco</option>
                        <option value="PINTADO_NEGRO">Pintado negro</option>
                        <option value="ANODIZADO_NATURAL">Anodizado natural</option>
                        <option value="ELECTROCOLOR_NEGRO">Electrocolor negro</option>
                    </select>
                </label>
            </div>

            <button type="submit" class="btn primario">Agregar y calcular</button>
        </form>
    </section>


    <section class="tarjeta bloque-form">
        <h2>Agregar abertura · Corrediza Domina (2 hojas)</h2>
        <p class="ayuda">
            Marco perimetral 9500, hojas 9520/9525 y parante central según el tipo de enganche.
        </p>

        <form method="post" action="{{ route('aberturas.store', $obra) }}">
            @csrf
            <input type="hidden" name="tipo" value="ABERTURA_SIMPLE">
            <input type="hidden" name="idTipologia" value="3">
            <input type="hidden" name="idSerie" value="2">

            <div class="grilla">
                <label>
                    Ancho (mm)
                    <input type="number" name="ancho" value="1500" min="100" max="10000" required>
                </label>

                <label>
                    Alto (mm)
                    <input type="number" name="alto" value="1000" min="100" max="10000" required>
                </label>

                <label>
                    Cantidad
                    <input type="number" name="cantidad" value="1" min="1" max="999" required>
                </label>

                <label>
                    Enganche central
                    <select name="engancheDomina">
                        <option value="SIMPLE">Simple · 2 parantes 9530</option>
                        <option value="REFORZADO">Reforzado · 9530 + 9540</option>
                        <option value="DOBLEMENTE REFORZADO">Doblemente reforzado · 2 × 9540</option>
                    </select>
                </label>

                <label>
                    Divisor al medio
                    <select name="divisorAlMedio">
                        <option value="0">No (un vidrio por hoja)</option>
                        <option value="1">Sí (dos vidrios por hoja)</option>
                    </select>
                </label>

                <label>
                    Vidrio
                    <select name="vidrioId">
                        <option value="">Sin vidrio</option>
                        @foreach ($vidrios as $vidrio)
                            <option value="{{ $vidrio['id'] }}">{{ $vidrio['etiqueta'] }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Ubicación
                    <input type="text" name="ubicacion" maxlength="60" placeholder="Ej.: Dormitorio">
                </label>

                <label>
                    Terminación
                    <select name="terminacion">
                        <option value="PINTADO_BLANCO">Pintado blanco</option>
                        <option value="PINTADO_NEGRO">Pintado negro</option>
                        <option value="ANODIZADO_NATURAL">Anodizado natural</option>
                        <option value="ELECTROCOLOR_NEGRO">Electrocolor negro</option>
                    </select>
                </label>
            </div>

            <button type="submit" class="btn primario">Agregar y calcular</button>
        </form>
    </section>


    <section class="tarjeta bloque-form">
        <h2>Agregar abertura · Corrediza HA 62 (2 hojas)</h2>
        <p class="ayuda">
            Marco a 45° (ADR 2464) o a 90° con 2 guías (ADR 1767/1769), hojas ADR 1770 y vidrio simple o DVH.
        </p>

        <form method="post" action="{{ route('aberturas.store', $obra) }}">
            @csrf
            <input type="hidden" name="tipo" value="ABERTURA_SIMPLE">
            <input type="hidden" name="idTipologia" value="3">
            <input type="hidden" name="idSerie" value="10">

            <div class="grilla">
                <label>
                    Ancho (mm)
                    <input type="number" name="ancho" value="1500" min="100" max="10000" required>
                </label>
                <label>
                    Alto (mm)
                    <input type="number" name="alto" value="1000" min="100" max="10000" required>
                </label>
                <label>
                    Cantidad
                    <input type="number" name="cantidad" value="1" min="1" max="999" required>
                </label>
                <label>
                    Marco
                    <select name="marcoHA62">
                        <option value="45 GRADOS">45 grados · ADR 2464</option>
                        <option value="90 GRADOS">90 grados · 2 guías</option>
                    </select>
                </label>
                <label>
                    Parante central
                    <select name="engancheHA62">
                        <option value="SIMPLE">Simple · 2 × ADR 2465</option>
                        <option value="REFORZADO">Reforzado · 2465 + 1771</option>
                        <option value="DOBLEMENTE REFORZADO">Doblemente · 2 × 1771</option>
                    </select>
                </label>
                <label>
                    Vidrio
                    <select name="vidrioId">
                        <option value="">Sin vidrio</option>
                        @foreach ($vidrios as $vidrio)
                            <option value="{{ $vidrio['id'] }}">{{ $vidrio['etiqueta'] }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Ubicación
                    <input type="text" name="ubicacion" maxlength="60" placeholder="Ej.: Cocina">
                </label>
                <label>
                    Terminación
                    <select name="terminacion">
                        <option value="PINTADO_BLANCO">Pintado blanco</option>
                        <option value="PINTADO_NEGRO">Pintado negro</option>
                        <option value="ANODIZADO_NATURAL">Anodizado natural</option>
                        <option value="ELECTROCOLOR_NEGRO">Electrocolor negro</option>
                    </select>
                </label>
            </div>

            <button type="submit" class="btn primario">Agregar y calcular</button>
        </form>
    </section>


    <section class="tarjeta bloque-form">
        <h2>Agregar abertura · Batiente (1 o 2 hojas)</h2>
        <p class="ayuda">
            Marco 6210, hoja 6211 (recta) o 6234 (curva), contravidrio según hoja y DVH.
        </p>

        <form method="post" action="{{ route('aberturas.store', $obra) }}">
            @csrf
            <input type="hidden" name="tipo" value="ABERTURA_SIMPLE">
            <input type="hidden" name="idTipologia" value="2">

            <div class="grilla">
                <label>
                    Serie
                    <select name="idSerie" required>
                        @foreach ($seriesBatiente as $serie)
                            <option value="{{ $serie['id'] }}">{{ $serie['nombre'] }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Ancho (mm)
                    <input type="number" name="ancho" value="800" min="100" max="10000" required>
                </label>
                <label>
                    Alto (mm)
                    <input type="number" name="alto" value="2000" min="100" max="10000" required>
                </label>
                <label>
                    Cantidad
                    <input type="number" name="cantidad" value="1" min="1" max="999" required>
                </label>
                <label>
                    Hojas
                    <select name="cantHojasBatiente">
                        <option value="1">1 hoja</option>
                        <option value="2">2 hojas</option>
                    </select>
                </label>
                <label>
                    Hoja
                    <select name="hojaBatiente">
                        <option value="RECTA">Recta · 6211</option>
                        <option value="CURVA">Curva · 6234</option>
                    </select>
                </label>
                <label>
                    Contravidrios
                    <select name="contravidriosBatiente">
                        <option value="RECTOS">Rectos</option>
                        <option value="CURVOS">Curvos</option>
                    </select>
                </label>
                <label>
                    Cierre
                    <select name="cierreBatiente">
                        <option value="FALLEBA">Falleba</option>
                        <option value="ALDABILLA">Aldabilla</option>
                    </select>
                </label>
                <label>
                    Color de herrajes
                    <select name="colorHerrajes">
                        <option value="BLANCO">Blanco</option>
                        <option value="NEGRO">Negro</option>
                    </select>
                </label>
                <label>
                    Vidrio
                    <select name="vidrioId">
                        <option value="">Sin vidrio</option>
                        @foreach ($vidrios as $vidrio)
                            <option value="{{ $vidrio['id'] }}">{{ $vidrio['etiqueta'] }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Ubicación
                    <input type="text" name="ubicacion" maxlength="60" placeholder="Ej.: Baño">
                </label>
                <label>
                    Terminación
                    <select name="terminacion">
                        <option value="PINTADO_BLANCO">Pintado blanco</option>
                        <option value="PINTADO_NEGRO">Pintado negro</option>
                        <option value="ANODIZADO_NATURAL">Anodizado natural</option>
                        <option value="ELECTROCOLOR_NEGRO">Electrocolor negro</option>
                    </select>
                </label>
            </div>

            <button type="submit" class="btn primario">Agregar y calcular</button>
        </form>
    </section>


    <section class="tarjeta bloque-form">
        <h2>Agregar abertura · Corrediza Doppia (2+1 / 2+2)</h2>
        <p class="ayuda">
            2+1: dos hojas móviles y un paño fijo. 2+2: cuatro hojas móviles en dos guías.
        </p>

        <form method="post" action="{{ route('aberturas.store', $obra) }}">
            @csrf
            <input type="hidden" name="tipo" value="ABERTURA_SIMPLE">
            <input type="hidden" name="idSerie" value="3">

            <div class="grilla">
                <label>
                    Variante
                    <select name="idTipologia">
                        <option value="14">2+1 · dos móviles + paño fijo</option>
                        <option value="15">2+2 · cuatro móviles</option>
                    </select>
                </label>
                <label>
                    Ancho (mm)
                    <input type="number" name="ancho" value="2400" min="100" max="10000" required>
                </label>
                <label>
                    Alto (mm)
                    <input type="number" name="alto" value="1500" min="100" max="10000" required>
                </label>
                <label>
                    Cantidad
                    <input type="number" name="cantidad" value="1" min="1" max="999" required>
                </label>
                <label>
                    Marco inferior
                    <select name="marcoInferiorDoppia">
                        <option value="ADR 2494">ADR 2494 · umbral bajo</option>
                        <option value="ADR 2496">ADR 2496 · umbral alto</option>
                    </select>
                </label>
                <label>
                    Parante lateral
                    <select name="paranteDoppia">
                        <option value="ADR 2474">ADR 2474 · simple</option>
                        <option value="ADR 2479">ADR 2479 · reforzado</option>
                    </select>
                </label>
                <label>
                    Zócalo y cabezal
                    <select name="zocaloDoppia">
                        <option value="ADR 2204">ADR 2204</option>
                        <option value="ADR 2209">ADR 2209 · alto</option>
                    </select>
                </label>
                <label>
                    Enganche central
                    <select name="engancheDoppia">
                        <option value="SIMPLE">Simple · 2 × ADR 2473</option>
                        <option value="REFORZADO">Reforzado · 2473 + 2479</option>
                        <option value="DOBLEMENTE REFORZADO">Doblemente · 2 × 2479</option>
                    </select>
                </label>
                <label>
                    Color de herrajes
                    <select name="colorHerrajes">
                        <option value="BLANCO">Blanco</option>
                        <option value="NEGRO">Negro</option>
                    </select>
                </label>
                <label>
                    Vidrio
                    <select name="vidrioId">
                        <option value="">Sin vidrio</option>
                        @foreach ($vidrios as $vidrio)
                            <option value="{{ $vidrio['id'] }}">{{ $vidrio['etiqueta'] }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Ubicación
                    <input type="text" name="ubicacion" maxlength="60" placeholder="Ej.: Estar">
                </label>
                <label>
                    Terminación
                    <select name="terminacion">
                        <option value="PINTADO_BLANCO">Pintado blanco</option>
                        <option value="PINTADO_NEGRO">Pintado negro</option>
                        <option value="ANODIZADO_NATURAL">Anodizado natural</option>
                        <option value="ELECTROCOLOR_NEGRO">Electrocolor negro</option>
                    </select>
                </label>
            </div>

            <button type="submit" class="btn primario">Agregar y calcular</button>
        </form>
    </section>


    <section class="tarjeta bloque-form">
        <h2>Agregar abertura · Corrediza + Paño Fijo Nórdical</h2>
        <p class="ayuda">
            Una o dos hojas corredizas con un paño fijo al lado (ADR 4251/4252, jambas 3913 y 4253).
        </p>

        <form method="post" action="{{ route('aberturas.store', $obra) }}">
            @csrf
            <input type="hidden" name="tipo" value="ABERTURA_SIMPLE">
            <input type="hidden" name="idTipologia" value="23">
            <input type="hidden" name="idSerie" value="44">

            <div class="grilla">
                <label>
                    Ancho (mm)
                    <input type="number" name="ancho" value="1800" min="100" max="10000" required>
                </label>
                <label>
                    Alto (mm)
                    <input type="number" name="alto" value="1400" min="100" max="10000" required>
                </label>
                <label>
                    Cantidad
                    <input type="number" name="cantidad" value="1" min="1" max="999" required>
                </label>
                <label>
                    Hojas corredizas
                    <select name="cantHojasNordical">
                        <option value="1">1 hoja + paño fijo</option>
                        <option value="2">2 hojas + paño fijo</option>
                    </select>
                </label>
                <label>
                    Vidrio
                    <select name="vidrioId">
                        <option value="">Sin vidrio</option>
                        @foreach ($vidrios as $vidrio)
                            <option value="{{ $vidrio['id'] }}">{{ $vidrio['etiqueta'] }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Ubicación
                    <input type="text" name="ubicacion" maxlength="60" placeholder="Ej.: Frente">
                </label>
                <label>
                    Terminación
                    <select name="terminacion">
                        <option value="PINTADO_BLANCO">Pintado blanco</option>
                        <option value="PINTADO_NEGRO">Pintado negro</option>
                        <option value="ANODIZADO_NATURAL">Anodizado natural</option>
                        <option value="ELECTROCOLOR_NEGRO">Electrocolor negro</option>
                    </select>
                </label>
            </div>

            <button type="submit" class="btn primario">Agregar y calcular</button>
        </form>
    </section>

    <script>
        (function () {
            const serie = document.getElementById('serie');
            const urlBase = @json(url('/aberturas/opciones/6'));

            serie?.addEventListener('change', async function () {
                const respuesta = await fetch(urlBase + '/' + serie.value, { headers: { Accept: 'application/json' } });
                if (!respuesta.ok) { return; }
                const datos = await respuesta.json();

                const llenar = (id, valores, vacio) => {
                    const select = document.getElementById(id);
                    if (!select) { return; }
                    select.innerHTML = '';
                    if (vacio) {
                        const opcion = document.createElement('option');
                        opcion.value = '';
                        opcion.textContent = vacio;
                        select.append(opcion);
                    }
                    valores.forEach((valor) => {
                        const opcion = document.createElement('option');
                        opcion.value = typeof valor === 'string' ? valor : valor.codigo;
                        opcion.textContent = typeof valor === 'string' ? valor : valor.codigo + ' · ' + valor.descripcion;
                        select.append(opcion);
                    });
                };

                llenar('perfilMarco', datos.perfiles);
                llenar('perfilContravidrio', datos.contravidrios, 'Sin contravidrio');
                llenar('perfilDivisor', datos.divisores, 'Sin divisor');
            });
        })();
    </script>
@endsection
