> **Nota de contexto:** este informe se generó sobre el código decompilado del JAR original
> (`Alucal_v5.5/_decompiled/`), que no se versiona por ser reproducible. Las rutas
> `AberturaService*.java:línea` se refieren a ese árbol decompilado; para regenerarlo ver
> [docs/analisis-java.md](../analisis-java.md).

# Especificación exacta del cálculo de precio — Alucal v5.5 (motor `MARGEN_OPERATIVO`)

Rutas relativas a `Alucal_v5.5/_decompiled/`. Motor: `services/precios/ServiceCalcularPrecio_MargenOperativo.java` (1174 líneas). Se selecciona por `Propiedades.CRITERIO_CALCULO_DE_PRECIO` (`gui/dialogs/DialogGenerarPresupuestoResumido_2.java:213-224`); valor shipped = `MARGEN_OPERATIVO`.

**Hallazgo transversal:** las 10 clases `entities/DetallePrecio*.java` **no se usan** (0 referencias; ninguna `new DetallePrecio*(...)` en todo el árbol). El desglose real que se muestra/reporta lo arman los `gui/panels/detalleprecio/*` y `services/reportes/entities/Data*`. Se documentan igual porque fueron pedidas.

---

## 1. Modelo de datos del cálculo

### 1.1 Clases `DetallePrecio*` (DTO de presentación, sin tabla)

| Clase | Campos (tipo) | Significado |
|---|---|---|
| `DetallePrecioAberturaSimple` | `moneda` String; `descripcionAbertura` String; `detallePrecioMarcoYHoja` DetallePrecioMarcoHojaAbSimple; `listDetallePrecioVidrio` List; `detallePrecioMosquitero`; `detallePrecioPremarco`; `detallePrecioTapajuntas`; `detallePrecioColocacion`; `coefIVA` double = **-1.0**; `cotizacion` double = **-1.0** | Totales de una abertura simple. `getPrecio()`(:130) suma y multiplica `coefIVA` y `cotizacion` **solo si > 0** |
| `DetallePrecioAberturaCompuesta` | `moneda`; `listDetallePrecioAberturaSimple`; `detallePrecioConectores`; `detallePrecioPremarco`; `detallePrecioTapajuntas`; `detallePrecioColocacion`; `coefIVA`=-1.0; `cotizacion`=-1.0 | Idem compuesta (`getPrecio()`:100) |
| `DetallePrecioMarcoHojaAbSimple` | `pesoKg`, `coefDesp`, `precioKg`, `costoAcc`, `margenOperativo` (double) | Bloque marco+hoja |
| `DetallePrecioMosquitero` | `pesoKg`, `coefDesp`, `precioKg`, `costoAcc`, `margenOperativo` | Bloque mosquitero |
| `DetallePrecioConectores` | `pesoKg`, `coefDesp`, `precioKg`, `margenOperativo` | Conectores entre paños (sin accesorios) |
| `DetallePrecioVidrio` | `descripcionVidrio` String; `precioM2`, `precioMinPorPieza` double; `cantPiezas` int; `anchoEnMetros`, `altoEnMetros` double | Una pieza de vidrio (precioM2 ya incluye margen) |
| `DetallePrecioPremarco` | `perimetroEnMetros`, `precioPorMetro` | Premarco |
| `DetallePrecioTapajuntas` | `perimetroEnMetros`, `precioPorMetro` | Tapajuntas (idéntico a premarco) |
| `DetallePrecioColocacion` | `precioBase`, `perimetroEnMetros`, `precioPorMetro` | Mano de obra de colocación |
| `DetallePrecioBaranda` | (ninguno) | `getPrecio()` devuelve 0 — stub |

Ninguna corresponde a tabla: son DTO en memoria. `IDetallePrecio` solo lo referencia `services/reportes/ItemPresupuestoParticular.java:18` (campo que nunca se instancia).

### 1.2 Entidades reales consumidas por el motor

| Entidad | Campos usados | Derivación |
|---|---|---|
| `AberturaSimple` / `AberturaCompuesta_2` (implementan `IAbertura`) | `perfiles`, `accesorios`, `vidrios`/`cantVidrios`, `tipologia`, `serie`, `paramGenerales.terminacion`, `anchoEnMM`, `altoEnMM`, `cantidad`, `cantHojas`, `cantDivisores`, `acristalamiento`, param. particulares UniblockMix, flags premarco/tapajuntas | `AberturaSimple.getAberturasSimples()` devuelve **`[this]`** y `getConectores()` lista vacía (AberturaSimple.java:165-172) → **una sola función sirve para simple y compuesta** |
| `CantBarras` | `perfil` (Perfil), `cantidad` int, `largo` int **mm**, `parte` PartesAbertura, `terminacion` | `getPesoBarra()` = `cantidad × (largo/1000) × perfil.getPeso()` → **kg** (CantBarras.java:120) |
| `CantAccesorios` | `accesorio`, `cantidad` double, `parte` | `getPrecioTotal()` = `accesorio.getPrecioUnitario() × cantidad` (CantAccesorios.java:69) |
| `Accesorio` | `precio` double, `cantPrecio` int, `cantHorasColocacion` | `getPrecioUnitario()` = **`precio / cantPrecio / 100`** (Accesorio.java:98) ← el `/100` es conversión de centavos |
| `CantVidrios` | `vidrio` (Vidrio2), `cantidad` int, `alto` double, `ancho` double | **ancho/alto ya en METROS** (`new CantVidrios(..., alto/1000.0, ancho/1000.0, ...)`, DialogCantVidrios_2.java:121) |
| `VidrioSinCamara` | `precioPorM2`, `precioMinimo`, `margen` | Costo/m² **sin** margen; el margen se aplica en la fórmula |
| `DVH` | `vidrioExterior`, `vidrioInterior` (VidrioSinCamara), `espesorCamaraEnMM`, `costoPorPerimetro` | `costoPorPerimetro` = coeficiente `COSTO_POR_METRO_CAMARA_{6,9,12,16}` casteado a int (DaoDVH.java:14-29) |
| `TerminacionRPT(terminacion, rpt)` | — | clave de mapa (equals por ambos) |
| `TipologiaSerie(tipologia, serie)` | — | clave de mapa (equals por idTipologia+idSerie) |

`PartesAbertura` = `{PREMARCO, TAPAJUNTAS, MARCO, HOJA, MOSQUITERO, CONECTOR, UNIBLOCK_MIX}`.

### 1.3 Tablas (SQLite `BDConfiguracion.s3db` / `BDVidrios.s3db`)

| Tabla | Columnas relevantes | Uso |
|---|---|---|
| `PreciosPorKilo` | `terminacion`, `rpt`, `valor` | precio U$S/kg por terminación+RPT |
| `MargenesDesperdiciosCoefAjuste` | `idTipologia`, `idSerie`, `MargenMH`, `MargenMosq`, `DespMH`, `DespMosq`, `CoefAjusteObraParticular` (+ columnas `*Fabrica`) | Coef. por tipología×serie |
| `CoeficientesDeCalculoDePrecio` | `clave`, `valor` (varchar) | 28 coeficientes globales |
| `PrecioColocacion` | `tipoColocacion`, `precioBase`, `precioPorMetroLineal` (int) | Mano de obra |
| `CostoUniblockMixArmado` | `enumCajon`, `enumCortina`, `costoMinimo`, `costoMetroCuadrado`, `costoLargoCajon`, `costo2doComando` | Uniblock cajón armado |
| `PrecioMetroLinealPremarco` / `...Tapajuntas` | `perfil`, `precio` | precio por metro por código de perfil |
| `BDVidrios.VidrioSinCamara` | `precio`, `precioMinimo`, `margen` | costo y margen del vidrio |
| `Propiedades` | `clave`, `valor` | `COSTO_HORA_TALLER`, `CRITERIO_CALCULO_*`, luces |

---

## 2. Pipeline (abertura simple y compuesta)

Precondición: `AberturaSimple` con `perfiles`, `accesorios`, `vidrios`, `tipologia`, `serie`, `terminacion`, medidas y cantidad ya calculados por `services/AberturaService*.java` (fuera de alcance).

| # | Paso | Método | Archivo:línea |
|---|---|---|---|
| 0 | Construir servicio con todos los coeficientes desde BD | `FactoryServiceCalcularPrecio.createServiceCalcularPrecioMargenOperativo()` | `services/precios/FactoryServiceCalcularPrecio.java:17-45` |
| 1 | Entrada: `getPrecio(elementoObra)` → si es `IAbertura` usa el cálculo de abertura | `getPrecio` | `ServiceCalcularPrecio_MargenOperativo.java:244-249` |
| 2 | Inicializar acumuladores (`precioRetorno`, `kgTotales`, `costoAccTotales`, `m2TotalesDeAcristalamiento`, `precioAlumXkiloTotal`, `margenXPrecioAlumAccTotal`, `margenXm2Vidrio`, `precioTotalAbSimples`, `precioTotalAlumAcc`) | `getPrecioYCoeficientesDeCalculoDeAbertura` | `:338-348` |
| 3 | **Por cada `AberturaSimple`**: separar kg en MH (`MARCO`+`HOJA`) y MOSQ (`MOSQUITERO`) | `:349-360` |
| 4 | Separar accesorios en `accMH` / `accMosq` con `getPrecioTotal()` | `:361-371` |
| 5 | Resolver `coefDespMH`, `coefDespMosq`, `margenMH`, `margenMosq` por `TipologiaSerie` (con fallback, §3.7); `precioPorKilo` por `TerminacionRPT` (con fallback, §3.8) | `:372-377` |
| 6 | `precioMH` y `precioMosq` | `:378-379` |
| 7 | Acumular kg, costo accesorios, precio alu y precio bruto alu+acc | `:380-383` |
| 8 | **Acristalamiento**: por cada `CantVidrios`, rama `VidrioSinCamara` o `DVH` | `:384-414` |
| 9 | **UniblockMix** si `paramUniblockMix != null && isLlevaUniblockMix()`: suma accesorios de parte `UNIBLOCK_MIX` × margen (o fórmula cajón armado) | `:415-420` (helper `:296-305`, `getPrecioUniblockMix` `:656-695`) |
| 10 | `precioAbSimple` = suma de los 4 componentes **redondeados individualmente**; sumar a `precioRetorno` | `:421-424` |
| 11 | **Conectores** (`abertura.getConectores()`, solo compuestas): kg×desp×precioKg×margen; redondear la suma una vez y sumar | `:426-439` |
| 12 | **Premarco** (si `llevaPremarco()`): `perimPremarco/1000 × precioMetro[código] × coefAjuste` con `coefAjuste=1.0` local | `:440-447` |
| 13 | **Tapajuntas** (si `llevaTapajuntas()`): ídem sobre `perimTapajuntas`; se acumula en la **misma** variable `precioPremarco` | `:448-453` |
| 14 | Sumar premarco+tapajuntas redondeados y devolver `int[6]` | `:454-460` |
| 15 | (Fuera del motor) multiplicar por moneda / IVA / coef. ajuste de obra | `getPrecioConsiderandoMonedImpuestosYCoefAjuste` `:252-261`; UI `gui/dialogs/DialogOpcionesAvanzadasPresupuestoElementoObra.java:391-412` |
| 16 | (Fuera) `importe = precioUnitario × cantidad` | `DialogGenerarPresupuestoElementoObra_2.java:285` |

**Recargos opcionales** (mosquitero, UniblockMix, premarco+tapajuntas, colonial, pintado, DVH) **no** forman parte del precio base: se calculan como **diferencia** `getPrecio(con) - getPrecio(sin)` (llamadas que mutan una copia) y se suman como cargos, cada uno re-escalado por moneda/IVA/ajuste (`gui/panels/PanelPrecioAberturaSimple_2.java:302-420`). Detalle en §3.9.

---

## 3. Fórmulas

Notación: kg = peso en kg; `m2` = ancho_m × alto_m.

### 3.1 Marco+Hoja y Mosquitero (`:378-379`)
```java
precioMH   = (kgMH * despMH * precioPorKilo + accMH) * margenMH;
precioMosq = (kgMosq * despMosq * precioPorKilo + accMosq) * margenMosq;
```
- `kgMH = Σ pesoBarra` de barras con parte `MARCO`/`HOJA`; `kgMosq` de `MOSQUITERO`. `pesoBarra = cantidad × largo_mm/1000 × pesoPerfil(kg/m)`.
- `accMH = Σ cantAccesorios.getPrecioTotal()` de parte `MARCO`/`HOJA`; `accMosq` de `MOSQUITERO`. `precioTotal = precioUnitario × cantidad`, `precioUnitario = precio/cantPrecio/100`.
- **Ninguno de los dos aplica `DESCUENTO_ACCESORIOS_OBRA`** (ver §3.10, inconsistencia).
- Fórmula mostrada al usuario: `"(Kg. x coefDesp x U$SKg. + U$SAcc" + (coefDesc!=1? " x coefDesc":"") + ") x Margen"` (`:1023`).

### 3.2 Acristalamiento — `VidrioSinCamara` (`:388-395`)
```java
m2PorPieza = vidrio.getAncho() * vidrio.getAlto();           // metros
precio     = Math.max(m2PorPieza * costoM2 * margen, precioMin) * vidrio.getCantidad();
margenXm2Vidrio              += m2PorPieza * cantidad * margen;
m2TotalesDeAcristalamiento   += m2PorPieza * cantidad;
```
`costoM2 = VidrioSinCamara.getCostoPorM2()` (columna `precio`), `precioMin = getPrecioMinimo()`, `margen = getMargen()`.
→ `precio = max(m² × costo_m2 × margen, mínimo_por_pieza) × piezas`. El mínimo es **por pieza**, no por m².

### 3.3 Acristalamiento — `DVH` (`:396-409`)
```java
coefDespVidrio   = this.getCoefDespVidrio();                  // 1.2
costoExt         = dvh.getVidrioExterior().getCostoPorM2() * coefDespVidrio * m2PorPieza;
costoInt         = dvh.getVidrioInterior().getCostoPorM2() * coefDespVidrio * m2PorPieza;
costoCamara      = dvh.getCostoPorPerimetro() * (vidrio.getAncho() + vidrio.getAlto()) * 2.0;
costoParaElFabricante  = costoExt + costoInt + costoCamara;
costoParaLaCarpinteria = costoParaElFabricante * margenFabricante;   // 1.0
precio = costoParaLaCarpinteria * margenCarpinteria * cantidad;      // 1.22
margenXm2Vidrio            += m2PorPieza * cantidad * (margenExt + margenInt);
m2TotalesDeAcristalamiento += m2PorPieza * cantidad * 2.0;
```
- `costoPorPerimetro` = `COSTO_POR_METRO_CAMARA_6|9|12|16` (8/10/12/16) casteado a `int`; se multiplica por **el perímetro en metros** `(ancho+alto)*2`.
- DVH **no** usa `precioMinimo` ni `margen` de cada vidrio para el precio: usa `MARGEN_DVH_FABRICANTE` × `MARGEN_DVH_CARPINTERIA`, en ese orden.
- El coeficiente de desperdicio de vidrio se aplica a **ambos** vidrios pero **no** a la cámara.

### 3.4 UniblockMix dentro del precio de la abertura (`:415-420`)
```java
costoYMargen = getCostoYMargenUniblockMix(anchoMM, altoMM, /*medidasDelVano*/ true, cajon, cortina);
precioTotalAlumAcc += costoYMargen[0];                     // costo crudo, sin margen
precioUniblockMix   = (int)Math.round(costoYMargen[0] * costoYMargen[1]);
```
- `costoYMargen[0] = Σ accesorios de UniblockMix` (`AberturaService.getAccesoriosDeUniblockMix`, `:297-301`); `[1] = margen` del mapa cajón×cortina (`:302-303`, `:307-316`).
- **`medidasDelVano` está hardcodeado `true`** en esta ruta, sin consultar `EnumMedidas` del parámetro.
- Variante cajón armado (`:277-294` y `:667-682`):
```java
ancho_m = anchoMM/1000; alto_m = altoMM/1000;
if (!medidasDelVano) { ancho_m += 0.08; alto_m += cajon.getAltoCajon()/1000.0; }
precio = ( Math.max(ancho_m*alto_m*costoMetroCuadrado + ancho_m*costoLargoCajon, costoMinimo)
           + costo2doComando ) * margen;
```
- `getMargenOperativoUniblockMix` (DaoConfiguracion:559-576): `cajon.esAluminio() ? (cortina==ALUMINIO ? MARGEN_UNIBLOCK_ALUM_ALUM : ..._ALUM_PVC) : (cortina==ALUMINIO ? ..._PVC_ALUM : ..._PVC_PVC)`.

### 3.5 Conectores (`:426-437`)
```java
precioConectores += kg * coefDespConectores * precioPorKilo * margenOperativoConectores;
// kg = conector.getPesoBarra(); precioPorKilo por TerminacionRPT(conector.terminacion, conector.perfil.isRpt())
precioRetorno += (int)Math.round(precioConectores);   // un solo redondeo para toda la suma
```
Sin accesorios. Coef: `COEF_DESPERDICIO_CONECTORES=1.1`, `MARGEN_OPERATIVO_CONECTORES=2.0`.

### 3.6 Premarco y Tapajuntas (`:440-453`)
```java
double coefAjuste = 1.0;                                    // :440 — literal, NO es el coef. de obra
if (abertura.llevaPremarco()) {
    precioMetroPrem = _mapPrecioPremarco.get(abertura.getCodigoPremarco());
    perimPremarco   = abertura.getPerimetroPremarco() / 1000.0;      // int mm → m
    precioPremarco += perimPremarco * precioMetroPrem * coefAjuste;
}
if (abertura.llevaTapajuntas()) { ... precioPremarco += perimTapas * precioMetroTapas * coefAjuste; }
retorno[0] = precioRetorno += (int)Math.round(precioPremarco);   // un solo redondeo
```
`getPerimetroPremarco()` = `Σ largo × cantidad` de barras parte `PREMARCO` (AberturaSimple.java:318). Precio unitario de tabla `PrecioMetroLinealPremarco.perfil → precio` (ej. `4570→11.9`, `ADR 2461→17.0`).

### 3.7 Resolución de coeficientes por `TipologiaSerie` (`:940-964`)
```java
1) map.get(ts)                                        → si existe, gana
2) si serie.getId()==49 → map.get(new TipologiaSerie(tipologia, new Serie(42,...)))   // PRIMA 90/2 L → 90/2
3) si tipologia != null → PRIMER entry del mapa con la misma tipologia (iteración HashMap!)
4) null → el llamador usa default 1.0
```
Defaults: `getValorTipologiaSerieODefault(..., 1.0)` para `despMH`, `despMosq`, `margenMH`, `margenMosq`.

### 3.8 Resolución de precio por kilo (`:966-993`)
```java
1) map.get(new TerminacionRPT(terminacion, rpt))
2) map.get(new TerminacionRPT(terminacion, !rpt))       // mismo acabado, RPT invertido
3) primer entry con la misma terminacion
4) primer valor no nulo del mapa
5) null → default 0.0   (precioPorKilo = 0 si no hay match)
```

### 3.9 Recargos por diferencia (`:781-849`) y colocación (`:851-938`)
| Recargo | Fórmula | Línea |
|---|---|---|
| Mosquitero | `getPrecio(ab + mosquiteroPorDefecto) - getPrecio(ab)` | `:781-786` |
| UniblockMix | `getPrecio(ab + uniblock) - getPrecio(ab)` | `:788-793` |
| Premarco+Tapajuntas | `getPrecio(ab + ambos) - getPrecio(ab)`; devuelve `[precio, codPremarco, codTapajuntas]` | `:795-812` |
| **Colonial** | `max(COLONIAL_RECARGO_MINIMO, totalM2 × COLONIAL_RECARGO_POR_M2)` con `totalM2 = Σ ancho×alto×cantidad` | `:814-828` |
| Pintado | `getPrecio(ab con PINTADO_BLANCO) - getPrecio(ab)`; lanza `DatoInvalidoException` si ya es PINTADO | `:830-839` |
| DVH | `getPrecio(ab con DVH) - getPrecio(ab)` | `:841-849` |
| Colocación simple | `precios[0] + round((anchoMM+altoMM)*2/1000 × precios[1])` | `:933` |
| Colocación compuesta | idem sobre medidas de la compuesta | `:851-856` |
| Colocación mampara | devuelve **solo** `precios[0]` (sin per-metro), mapeando texto de "tipo mampara" a `EnumColocacion` (series 25 y 26) | `:878-932` |

### 3.10 Bloque "Precio Aluminio y Accesorios MH" (otra ruta, sí aplica descuento)
`private getPrecioAlumAccMH(AberturaSimple)` (`:995-1004`):
```java
return (int)Math.round((kgMH * coefDesp * precioPorKilo + costoAcc * _coefDescuentoAccObra) * margenMH);
```
Es el **único** lugar donde se usa `DESCUENTO_ACCESORIOS_OBRA`. `getPrecioAlumAccMHAbertura` (`:546-560`) lo usa; el precio total (`:338-461`) **no**. `costoAcc` aquí sale de `getPrecioAccesoriosPorParteAbertura` = `Σ cantidad × precioUnitario` (mismo valor que `getPrecioTotal`).

### 3.11 Índices devueltos `int[6]` (`:454-460`)
```java
[0] = precio final unitario (entero)
[1] = round(precioAlumXkiloTotal / kgTotales * 100)        // U$S/kg ×100  ("A")
[2] = round(margenXPrecioAlumAccTotal / precioTotalAlumAcc * 100)  // margen medio ×100 ("M")
[3] = round(margenXm2Vidrio / m2TotalesDeAcristalamiento * 100)    // margen vidrio ×100 ("V")
[4] = round(costoAccTotales / kgTotales * 100)             // U$S acc/kg ×100 ("R")
[5] = round(coefAjuste * 100) = 100                        // coef. ajuste ("C", siempre 100)
```
Ojo: `precioAlumXkiloTotal` y `costoAccTotales` **no** incluyen `coefDesp` ni márgenes; `margenXPrecioAlumAccTotal` **no** incluye premarco/tapajuntas. Son indicadores, no exactos.

### 3.12 Estructura de aluminio (`:463-532`) — no es abertura
```java
precioPorKiloPromedio = Σ(kilos_por_terminacion × precioKg) / totalKilos;
precioAlumAcc = (totalKilos * coefDespEstructura * precioPorKiloPromedio + costoAcc) * margenOperativoEstructura;
retorno[0] = round(precioAlumAcc * coefAjusteEstructura);
retorno[1] = round(precioAcristalamiento * coefAjusteEstructura);   // misma fórmula de vidrio que §3.2/3.3
```
`getPrecio` para estructura devuelve `retorno[0] + retorno[1]` (`:248`).

### 3.13 Motor alternativo `HORAS_TALLER` (no activo por defecto)
`ServiceCalcularPrecio_HorasTaller.java:202-210`:
```java
(costoAluminio + costoAccesorios) * _multiplicadorMateriales      // 1.2
  + horasTaller * _costoHoraTaller * _multiplicadorManoDeObra;    // 20.0 × 2.0
```
`horasTaller` = `ServiceCalcularHorasDeArmado.getCantidadDeHorasDeArmadoPorUnidad` (`:40-110`):
```java
// interpolación lineal por diagonal, con piso
a = (horasGrande - horasChica) / (diagGrande - diagChica);
b = horasChica - diagChica * a;
horas = max(minimo, sqrt(ancho²+alto²) * a + b);          // redondeado a 2 dec. (:54)
× 1.25 si tipología 3 y 3 hojas; ×1.4 si 4 hojas; ×1.8 si 2 hojas (otras tipologías)
× 1.5 si tipología 1 con opción "movil"; ×1.6 si tipología 8 con "movil"
+ ancho_m*alto_m*0.5 si acristalamiento entero TABLILLA; /2 por sector si DIVISOR_AL_MEDIO
+ cantDivisores * 0.25
+ Σ (cantAccesorio × accesorio.cantHorasColocacion)
× ajustePorCantidad[{1.0,0.93×3,0.85×6,0.78×10,0.7}]     // índice cantidad-1, tope 0.7
```
Conector: `cantidad × largo/1000 × 0.25` (`:112-115`).

### 3.14 Moneda / IVA / ajuste (`:252-261`)
```java
retorno = precioBase;
if (moneda == PESOS)   retorno *= cotizacion;
if (agregarIVA)        retorno *= 1.22;          // IVA 22% HARDCODEADO
retorno *= coefAjuste;
return (int)Math.round(retorno);
```
Orden: moneda → IVA → ajuste. Idéntico en `DialogOpcionesAvanzadasPresupuestoElementoObra.java:391-412` y en `ServiceCalcularPrecio_HorasTaller.java:180-189`. `DetallePrecio*` invierte el orden (coefIVA y luego cotización), pero es código muerto.

---

## 4. Entradas de configuración

### 4.1 `CoeficientesDeCalculoDePrecio` (tabla `clave`→`valor`, varchar)
| Clave | Valor shipped | Usado en |
|---|---|---|
| `COEF_DESPERDICIO_CONECTORES` | **1.1** | §3.5 |
| `MARGEN_OPERATIVO_CONECTORES` | **2.0** | §3.5 |
| `COEF_DESP_VIDRIO` | **1.2** | §3.3 |
| `MARGEN_DVH_FABRICANTE` | **1.0** | §3.3 |
| `MARGEN_DVH_CARPINTERIA` | **1.22** | §3.3 |
| `COLONIAL_RECARGO_MINIMO` | **50** | §3.9 (int) |
| `COLONIAL_RECARGO_POR_M2` | **35** | §3.9 (int) |
| `DESCUENTO_ACCESORIOS_OBRA` | **1.0** | solo §3.10 |
| `MARGEN_UNIBLOCK_ALUM_ALUM` | **1.45** | §3.4 |
| `MARGEN_UNIBLOCK_ALUM_PVC` | **1.5** | §3.4 |
| `MARGEN_UNIBLOCK_PVC_ALUM` | **1.6** | §3.4 |
| `MARGEN_UNIBLOCK_PVC_PVC` | **1.7** | §3.4 |
| `COSTO_POR_METRO_CAMARA_6/9/12/16` | **8 / 10 / 12 / 16** | §3.3 (vía `DaoDVH`) |
| `MARGEN_OPERATIVO_ESTRUCTURA` | **2.1** | §3.12 |
| `COEF_DESPERDICIO_ESTRUCTURA` | **1.08** | §3.12 |
| `COEF_AJUSTE_ESTRUCTURA` | **1.15** | §3.12 |
| `PRECIO_POR_KILO`, `MARGEN_OPERATIVO`, `COEF_DESPERDICIO`, `MARGEN_OPERATIVO_ACRIS`, `MARGEN_OPERATIVO_MOSQ`, `MARGEN_OPERATIVO_BARANDAS`, `PRECIO_POR_METRO_PREMARCO`, `PRECIO_POR_METRO_COLOCACION`, `PRECIO_PROM_METRO_ACC_PREM` | 7.5 / 1.8 / 1.08 / 2.0 / 2.6 / 2.5 / 11 / 14 / 2.5 | **0 referencias en el código decompilado** — claves muertas (se cargan igual en el cache estático, DaoConfiguracion:1631) |
| `MARGEN_DVH` (fila extra en BD) | 1.0 | no existe en el enum → ignorada |

**No hay valor por defecto en código** para estos coeficientes: `getCoeficienteDeCalculoDePrecio` lee `Propiedades("CoeficientesDeCalculoDePrecio", clave)` y hace `Double.parseDouble`; si falta la fila → `NoExisteException` → `ErrorDeConexionException` (DaoConfiguracion:552-557).

### 4.2 `MargenesDesperdiciosCoefAjuste` (por `idTipologia` × `idSerie`)
Se leen **solo** las columnas operativas `MargenMH`, `MargenMosq`, `DespMH`, `DespMosq`, `CoefAjusteObraParticular` (DaoConfiguracion:942-1131). Las columnas `*Fabrica` solo se usan en `restaurarMargenesOperativos()` (`UPDATE ... SET valor = valorFabrica`, :1240). Rango observado: `MargenMH` 1.7-3.2, `DespMH` 1.06-1.3, `MargenMosq` 0-6, `DespMosq` 0-1.3, `CoefAjusteObraParticular` 1.15 uniforme. No hay fila por defecto: si falta la combinación, el motor cae al fallback de §3.7 (o default 1.0).

### 4.3 Otras tablas
- `PreciosPorKilo`: 19 filas. Ej.: `ANODIZADO_NATURAL rpt=0 → 6`, `PINTADO_BLANCO rpt=0 → 7.48`, `ANODIZADO_NATURAL rpt=1 → 9.5`, `ELECTROCOLOR_NEGRO rpt=1 → 12.5`.
- `PrecioColocacion` (int): `ABERTURA_ATORNILLAR 20/12`, `ABERTURA_AMURAR 25/16`, `ABERTURA_SACAR_Y_COLOCAR 30/22`, `ABERTURA_DENTRO_DE_PREMARCO 10/10`, `MAMPARA_2_HOJAS 50/0`, `MAMPARA_3_HOJAS 60/0`, `MAMPARA_4_HOJAS 70/0`, `MAMPARA_PLEGABLE 60/0`, `MAMPARA_1_HOJA_FIJA 40/0`, `MAMPARA_3_HOJAS_EN_ANGULO 70/0`, `MAMPARA_4_HOJAS_EN_ANGULO 80/0`, `MAMPARA_2_HOJAS_EN_ANGULO 60/0`.
- `CostoUniblockMixArmado`: (PVC_155,PVC) 71.5/55/0/0; (PVC_155,ALUMINIO) 110.5/85/0/0; (ALUMINIO_185,PVC) 91.5/55/**30**/0; (ALUMINIO_185,ALUMINIO) 143/110/0/0 (min/m²/largoCajón/2doComando).
- `Propiedades`: `COSTO_HORA_TALLER=25` (**no se usa**: `_costoHoraTaller=20.0` hardcodeado, sin setter), `ESPESOR_MINIMO_DVH=0`, `CRITERIO_CALCULO_DE_PRECIO=MARGEN_OPERATIVO`, `CRITERIO_CALCULO_COSTO_UNIBLOCK_MIX=CAJON_ARMADO`, `LUCES_PREMARCO=4;0;4;4`, `SECCION_TAPAJUNTAS=COLOCACION`.

### 4.4 Constantes literales en código (no configurables)
`1.22` (IVA) · `1.2` materiales y `20.0` hora y `2.0` mano de obra (HorasTaller) · `0.08` (8 cm de sobre-medida de ancho en Uniblock) · `/1000.0` mm→m · `/100.0` centavos · `*100.0` para índices · `1.0` coefAjuste local de premarco · serie 42/49 (PRIMA 90/2) · array de ajuste por cantidad · multiplicadores de hojas (1.25/1.4/1.8/1.5/1.6) · `0.5` h/m² tablilla y `0.25` h/divisor y `0.25` h/m conector.

---

## 5. Casos borde y validaciones

1. **Redondeo por componente**: en la abertura se redondea **cada** bloque (`Math.round(precioMH) + Math.round(precioMosq) + Math.round(precioAcristalamiento) + Math.round(precioUniblockMix)`, `:422`) y luego se vuelve a redondear (`:423`). Conectores: un solo redondeo de la suma (`:438`). Premarco+tapajuntas: un solo redondeo de la suma (`:454`). Replicar exactamente: redondear por bloque cambia el total hasta ±2.
2. **`(int)Math.round(...)`** → redondeo half-up sobre `double`; `Math.round` devuelve `long` y el cast trunca a 32 bits.
3. **División por cero**: si `kgTotales == 0` (abertura sin perfiles) → `[1]` y `[4]` = `Integer.MAX_VALUE` (Infinity) o `0` si es `0/0` (NaN). Igual con `m2TotalesDeAcristalamiento == 0` → `[3]`. `getPrecioAlumAccMHAberturaSimple_FormulaSustituida` sí explota con NPE si falta el mapa.
4. **Vidrio desconocido**: `RuntimeException("No se conoce la clase de vidrio '...'")` (`:411`, `:520`, `:590`).
5. **Vidrio nulo**: `getPrecioAcristalamiento` devuelve **0 para toda la abertura** si algún `CantVidrios.getVidrio() == null` (`:568-570`), mientras que el camino de precio total lanzaría NPE.
6. **Acristalamiento desconocido** (solo HorasTaller): `RuntimeException("No se conoce el tipo de acristalamiento ...")` (`ServiceCalcularHorasDeArmado.java:99`).
7. **Mapas nulos**: `_mapPrecioPremarco.get(...)` sobre mapa nulo → NPE; `getPrecioPremarco`/`getPrecioTapajuntas` tienen variante que recarga del DAO (`:606-612`, `:625-631`) pero `getPrecioYCoeficientesDeCalculoDeAbertura` usa los campos directos sin chequeo.
8. **Código de premarco/tapajuntas inexistente en la tabla** → `NullPointerException` al unboxear (`:444`, `:450`).
9. **Colocación**: `getPrecioColocacion` atrapa `ErrorDeConexionException` y **devuelve `-1`** (`:935-937`). Mampara sin "tipo" identificable → `-1`.
10. **`getPrecioRecargoPorColonial`**: exige ≥1 vidrio, si no `DatoInvalidoException("Esta abertura no tiene vidrios")` (`:815-817`). Devuelve `max(50, m2×35)`; **no** verifica terminación "colonial" ni tipo de abertura — es un cargo optativo que la UI ofrece a toda abertura no-mampara (`PanelPrecioAberturaSimple_2.java:355-364`).
11. **Pintado**: `DatoInvalidoException` si la terminación ya es de tipo `PINTADO` (`:831-833`).
12. **UniblockMix**: si `cajon`, `cortina` o `medidas` son null, o no hay margen en el mapa, el bloque se **omite silenciosamente** (`:665`); en `getPrecioUniblockMix(IAbertura,...)` el `continue` deja el precio en 0.
13. **Fallback no determinista**: §3.7 paso 3 recorre `HashMap.entrySet()` y devuelve el primer valor con la misma tipología → **orden no especificado**; con varias series para la misma tipología el resultado puede variar entre ejecuciones/JVM.
14. **Precio por kilo faltante** → `0.0` (§3.8 paso 5) ⇒ el aluminio sale gratis, sin aviso.
15. **Descuento de accesorios**: `DESCUENTO_ACCESORIOS_OBRA` **no** se aplica al precio total (solo a §3.10). Si se porta tal cual, el precio final no cambia al tocar ese coeficiente.
16. **`CoefAjusteObraParticular`**: se carga en `_mapCoefAjuste` (`Factory:24`) y **nunca se lee** dentro del motor; el ajuste se toma de la UI (`PanelAberturaSimple_2.java:938`) y se aplica después con `getPrecioConsiderandoMonedImpuestosYCoefAjuste`. El `[5]` que devuelve el motor es siempre 100 (=1.0).
17. **Cantidad**: el motor calcula **precio unitario**; `importe = precioUnitario × elementoObra.getCantidad()` (`DialogGenerarPresupuestoElementoObra_2.java:285`). El motor de horas en cambio **sí** usa `getCantidad()` para el ajuste por cantidad ⇒ cambiar la cantidad no escala linealmente en ese criterio.
18. **Mínimos**: `precioMinimo` del vidrio es **por pieza** (no por m², no por abertura); `costoMinimo` de Uniblock es por abertura.
19. **`coefIVA`/`cotizacion` de `DetallePrecio*`**: se aplican solo si `> 0` (default -1.0 = sin efecto) — código muerto.
20. **`getPrecio` de estructura** suma `[0]+[1]` y ambos ya vienen multiplicados por `COEF_AJUSTE_ESTRUCTURA=1.15`; la abertura **no** tiene equivalente (su 1.15 de `MargenesDesperdiciosCoefAjuste` va por la UI).

---

## 6. Ambigüedades (no resolubles solo con el código decompilado)

1. **Valores "de fábrica" reales**: el código no contiene defaults; los valores de §4.1-4.3 salen de la BD shipped (`Alucal_v5.5.8/base_de_datos/BDConfiguracion.s3db`, versión `12`/`BD_VERSION 4.4`). No hay DDL ni script de seed en el artefacto: no se puede saber si la BD shipped es la de fábrica o fue editada por el usuario (hay `HASH`, `serial` y columnas `*Fabrica` que sugieren edición en campo).
2. **`MargenMH` vs `MargenMHFabrica`**: `restaurarMargenesOperativos()` copia fábrica→operativo, pero no hay evidencia de cuándo se invoca ni de qué columna es la "verdadera" en una instalación dada.
3. **Fallback por tipología** (§3.7 paso 3): imposible reproducir en PHP sin fijar un orden; el original depende del `HashMap`. Hay que decidir una regla determinista (p.ej. menor `idSerie`) y documentarla como divergencia.
4. **`getPrecioYCoeficientesDeCalculoDeAbertura` con `medidasDelVano=true` fijo** (§3.4) contradice el parámetro `EnumMedidas` del UniblockMix que sí respeta `getPrecioUniblockMix(IAbertura,...)` (`:676`). No se puede saber cuál es el comportamiento "correcto" pretendido; el precio mostrado en el panel de la abertura usa el `true` fijo.
5. **`precioTotalAlumAcc += costoYMargen[0]` sin margen** (`:418`): parece un bug (suma costo crudo a una base de precio) que distorsiona el índice `[2]`. No afecta `[0]`.
6. **`coefAjuste = 1.0` local en premarco/tapajuntas** (`:440`): parece un resto de cuando el ajuste de obra se aplicaba por bloque. No hay forma de saber si debía ser `getMapCoefAjuste().get(tipologiaSerie)`.
7. **Discrepancia UI vs motor en accesorios**: `PanelDetallePrecioMarcoYHojaAbSimple_2.java:170-177` muestra `(peso×desp×precioKg + costoAcc) × margen × coefAjuste` **sin** `DESCUENTO_ACCESORIOS_OBRA`, mientras `getPrecioAlumAccMH` sí lo aplica. Dos "verdades" para el mismo número.
8. **`DVH.costoPorPerimetro`**: el nombre dice perímetro, el DAO lo llena con `COSTO_POR_METRO_CAMARA_*` y la variable local se llama `recargoPrecioPorM2` (DaoDVH.java:15). La fórmula lo multiplica por el perímetro. No se puede determinar si el coeficiente está expresado por m² o por metro lineal de cámara; con los valores 8/10/12/16 y cámara de 6 mm, el resultado es plausible en ambas lecturas. **Dudoso.**
9. **DVH y `precioMinimo`**: no se aplica mínimo al DVH. No se sabe si es intencional.
10. **`margenXm2Vidrio` en DVH** usa `margenExt + margenInt` (márgenes de los vidrios) mientras el precio usa `MARGEN_DVH_FABRICANTE × MARGEN_DVH_CARPINTERIA`. El índice `[3]` no es comparable con el de `VidrioSinCamara`. No se puede saber cuál se quiso reportar.
11. **Semántica de `Accesorio.precio / cantPrecio / 100`**: el `/100` sugiere centavos, pero no hay evidencia de la moneda base (¿U$S?) ni de si `cantPrecio` es "unidades por precio" (ej. 100 = por ciento). Afecta a todos los accesorios.
12. **`ESPESOR_MINIMO_DVH = 0`**: se lee (`DaoConfiguracion:96`) pero no se usa en el motor; se desconoce su rol (¿validación de UI?).
13. **`SECCION_TAPAJUNTAS`, `LUCES_PREMARCO`, `INCLUIR_DETALLE_CALCULO`, `MOSTRAR_IVA`**: afectan la *presentación* del presupuesto, no el precio; su efecto exacto está en los generadores de reportes (fuera del alcance pedido).
14. **Origen de los pesos**: `perfil.getPeso()` (kg/m) viene de `BDDefinicionesAberturas`; no se validó contra el catálogo real. Si el peso es kg/m y `largo` está en mm, la conversión `/1000` es correcta, pero no hay test que lo confirme.
15. **Redondeo del precio unitario antes de multiplicar por cantidad**: el motor devuelve `int`; el `importe` se calcula con ese entero. No se puede saber si el original mostraba el subtotal sin redondear en algún reporte (los `Data*` de reportes recalculan).
16. **`ServiceCalcularPrecio_HorasTaller`**: `_costoHoraTaller=20.0` sin setter, pero `Propiedades.COSTO_HORA_TALLER=25`. No se puede determinar cuál se usa en producción (el campo nunca se asigna desde la BD ⇒ 20.0).
