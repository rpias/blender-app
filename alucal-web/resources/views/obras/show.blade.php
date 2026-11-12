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
        <h2>Agregar abertura</h2>
        <p class="ayuda">
            Elegí el tipo de abertura: el formulario muestra sólo los campos que corresponden y el
            precio se calcula al guardar.
        </p>

        <form method="post" action="{{ route('aberturas.store', $obra) }}" id="form-abertura">
            @csrf
            <input type="hidden" name="tipo" value="ABERTURA_SIMPLE">

            <div class="grilla">
                <label class="ancho-completo">
                    Tipo de abertura
                    <select name="variante" id="variante" required>
                        <optgroup label="Marco fijo">
                            <option value="6:10">Marco fijo · HA 62</option>
                            <option value="6:19">Marco fijo · Serie 20</option>
                            <option value="6:15">Marco fijo · Rotonda 640</option>
                        </optgroup>
                        <optgroup label="Corrediza">
                            <option value="3:2">Corrediza Domina (2 hojas)</option>
                            <option value="3:10">Corrediza HA 62 (2 hojas)</option>
                            <option value="3:19">Corrediza Serie 20 (2 hojas)</option>
                            <option value="14:3">Corrediza Doppia 2+1</option>
                            <option value="15:3">Corrediza Doppia 2+2</option>
                            <option value="23:44">Corrediza + paño fijo Nórdical</option>
                        </optgroup>
                        <optgroup label="Otras">
                            <option value="2:10">Batiente HA 62</option>
                            <option value="2:12">Batiente Prima</option>
                            <option value="10:10">Proyectante HA 62</option>
                            <option value="12:10">Tabaquera HA 62</option>
                            <option value="4:19">Guillotina Serie 20</option>
                        </optgroup>
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
                    Ubicación
                    <input type="text" name="ubicacion" value="{{ old('ubicacion') }}" maxlength="60" placeholder="Ej.: Dormitorio">
                </label>
                <label>
                    Terminación
                    <select name="terminacion">
                        <option value="PINTADO_BLANCO">Pintado blanco</option>
                        <option value="PINTADO_NEGRO">Pintado negro</option>
                        <option value="PINTADO_BRONCE_OSCURO">Pintado bronce oscuro</option>
                        <option value="PINTADO_MARRON_AFRICANO">Pintado marrón africano</option>
                        <option value="ANODIZADO_NATURAL">Anodizado natural</option>
                        <option value="ELECTROCOLOR_NEGRO">Electrocolor negro</option>
                        <option value="ELECTROCOLOR_BRONCE_OSCURO">Electrocolor bronce oscuro</option>
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
            </div>

            {{-- Marco fijo --}}
            <div class="grupo-variante" data-para="6:10 6:19 6:15">
                <div class="grilla">
                    <label>
                        Perfil de marco
                        <select name="perfilMarco" id="perfilMarco">
                            @foreach ($opcionesIniciales['perfiles'] as $perfil)
                                @php($codigoPerfil = is_array($perfil) ? $perfil['codigo'] : $perfil)
                                @php($descPerfil = is_array($perfil) ? $perfil['descripcion'] : $perfil)
                                <option value="{{ $codigoPerfil }}">{{ $codigoPerfil }} · {{ $descPerfil }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        Contravidrio
                        <select name="perfilContravidrio" id="perfilContravidrio">
                            <option value="">Sin contravidrio</option>
                            @foreach ($opcionesIniciales['contravidrios'] as $perfil)
                                @php($codigoContra = is_array($perfil) ? $perfil['codigo'] : $perfil)
                                <option value="{{ $codigoContra }}">{{ $codigoContra }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        Divisor
                        <select name="perfilDivisor" id="perfilDivisor">
                            <option value="">Sin divisor</option>
                            @foreach ($opcionesIniciales['divisores'] as $perfil)
                                @php($codigoDivisor = is_array($perfil) ? $perfil['codigo'] : $perfil)
                                <option value="{{ $codigoDivisor }}">{{ $codigoDivisor }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        Divisores horizontales
                        <input type="number" name="divisoresHorizontales" value="0" min="0" max="6">
                    </label>
                    <label>
                        Divisores verticales
                        <input type="number" name="divisoresVerticales" value="0" min="0" max="6">
                    </label>
                </div>
            </div>

            {{-- Corrediza Serie 20 --}}
            <div class="grupo-variante" data-para="3:19">
                <div class="grilla">
                    <label>
                        Marco lateral
                        <select name="marcoLateral">
                            <option value="235">235</option>
                            <option value="291">291</option>
                            <option value="205">205 · requiere uniblock</option>
                        </select>
                    </label>
                    <label>
                        Marco inferior
                        <select name="marcoInferior">
                            <option value="204">204</option>
                            <option value="236">236</option>
                        </select>
                    </label>
                    <label>
                        Zócalo
                        <select name="zocalo">
                            <option value="2210">2210</option>
                            <option value="2211">2211</option>
                            <option value="201">201</option>
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
                        Enganche
                        <select name="enganche">
                            <option value="203">203</option>
                            <option value="216">216</option>
                        </select>
                    </label>
                    <label>
                        Cantidad de enganches
                        <input type="number" name="cantEnganches" value="2" min="1" max="6">
                    </label>
                    <label class="casilla">
                        <input type="checkbox" name="cajaDeAgua" value="1"> Caja de agua
                    </label>
                    <label class="casilla">
                        <input type="checkbox" name="punzonadoManual" value="1"> Punzonado manual
                    </label>
                    <label class="casilla">
                        <input type="checkbox" name="divisorAlMedio" value="1"> Divisor al medio
                    </label>
                </div>
            </div>

            {{-- Domina --}}
            <div class="grupo-variante" data-para="3:2">
                <div class="grilla">
                    <label>
                        Enganche central
                        <select name="engancheDomina">
                            <option value="SIMPLE">Simple · 2 parantes 9530</option>
                            <option value="REFORZADO">Reforzado · 9530 + 9540</option>
                            <option value="DOBLEMENTE REFORZADO">Doblemente · 2 × 9540</option>
                        </select>
                    </label>
                    <label class="casilla">
                        <input type="checkbox" name="divisorAlMedio" value="1"> Divisor al medio
                    </label>
                </div>
            </div>

            {{-- HA 62 --}}
            <div class="grupo-variante" data-para="3:10">
                <div class="grilla">
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
                            <option value="SIMPLE">Simple · 2 × 2465</option>
                            <option value="REFORZADO">Reforzado · 2465 + 1771</option>
                            <option value="DOBLEMENTE REFORZADO">Doblemente · 2 × 1771</option>
                        </select>
                    </label>
                </div>
            </div>

            {{-- Batiente --}}
            <div class="grupo-variante" data-para="2:10 2:12">
                <div class="grilla">
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
                </div>
            </div>

            {{-- Doppia 2+1 y 2+2 --}}
            <div class="grupo-variante" data-para="14:3 15:3">
                <div class="grilla">
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
                        Zócalo
                        <select name="zocaloDoppia">
                            <option value="ADR 2204">ADR 2204</option>
                            <option value="ADR 2209">ADR 2209 · alto</option>
                        </select>
                    </label>
                    <label>
                        Enganche central
                        <select name="engancheDoppia">
                            <option value="SIMPLE">Simple</option>
                            <option value="REFORZADO">Reforzado</option>
                            <option value="DOBLEMENTE REFORZADO">Doblemente reforzado</option>
                        </select>
                    </label>
                    <label>
                        Color de herrajes
                        <select name="colorHerrajes">
                            <option value="BLANCO">Blanco</option>
                            <option value="NEGRO">Negro</option>
                        </select>
                    </label>
                </div>
            </div>

            {{-- Nórdical --}}
            <div class="grupo-variante" data-para="23:44">
                <div class="grilla">
                    <label>
                        Hojas corredizas
                        <select name="cantHojasNordical">
                            <option value="1">1 hoja + paño fijo</option>
                            <option value="2">2 hojas + paño fijo</option>
                        </select>
                    </label>
                </div>
            </div>

            {{-- Proyectante --}}
            <div class="grupo-variante" data-para="10:10">
                <div class="grilla">
                    <label>
                        Contravidrios
                        <select name="contravidriosProyectante">
                            <option value="RECTOS">Rectos · ADR 1655</option>
                            <option value="CURVOS">Curvos · desde 14 mm</option>
                        </select>
                    </label>
                    <label>
                        Color de herrajes
                        <select name="colorHerrajes">
                            <option value="BLANCO">Blanco</option>
                            <option value="NEGRO">Negro</option>
                        </select>
                    </label>
                </div>
            </div>

            {{-- Tabaquera --}}
            <div class="grupo-variante" data-para="12:10">
                <div class="grilla">
                    <label>
                        Contravidrios
                        <select name="contravidriosProyectante">
                            <option value="RECTOS">Rectos · ADR 1655</option>
                            <option value="CURVOS">Curvos · desde 14 mm</option>
                        </select>
                    </label>
                    <label>
                        Cierre
                        <select name="cierreTabaquera">
                            <option value="SAPITO">Sapito</option>
                            <option value="SIMPLÓN NEWTON">Simplón Newton</option>
                            <option value="SIMPLÓN TRADICIONAL">Simplón tradicional</option>
                        </select>
                    </label>
                    <label>
                        Color de herrajes
                        <select name="colorHerrajes">
                            <option value="BLANCO">Blanco</option>
                            <option value="NEGRO">Negro</option>
                        </select>
                    </label>
                </div>
            </div>

            {{-- Guillotina --}}
            <div class="grupo-variante" data-para="4:19">
                <div class="grilla">
                    <label>
                        Punzonado
                        <select name="punzonadoGuillotina">
                            <option value="0">Automático</option>
                            <option value="1">Manual</option>
                        </select>
                    </label>
                    <label>
                        Color de herrajes
                        <select name="colorHerrajes">
                            <option value="GRIS">Gris</option>
                            <option value="NEGRO">Negro</option>
                        </select>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn primario">Agregar y calcular</button>
        </form>
    </section>

    <script>
        (function () {
            const variante = document.getElementById('variante');
            const grupos = document.querySelectorAll('.grupo-variante');

            const mostrar = (valor) => {
                grupos.forEach((grupo) => {
                    const para = (grupo.dataset.para || '').split(' ');
                    grupo.hidden = !para.includes(valor);
                });
            };

            const cargarPerfiles = async (tipologia, serie) => {
                const respuesta = await fetch(`{{ url('/aberturas/opciones') }}/${tipologia}/${serie}`, {
                    headers: { Accept: 'application/json' },
                });

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
            };

            const aplicar = () => {
                mostrar(variante.value);
                const [tipologia, serie] = variante.value.split(':').map(Number);
                if (tipologia === 6) { cargarPerfiles(tipologia, serie); }
            };

            variante?.addEventListener('change', aplicar);
            aplicar();
        })();
    </script>
@endsection
