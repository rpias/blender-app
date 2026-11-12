> **Nota de contexto:** este informe se generó sobre el código decompilado del JAR original
> (`Alucal_v5.5/_decompiled/`), que no se versiona por ser reproducible. Las rutas
> `AberturaService*.java:línea` se refieren a ese árbol decompilado; para regenerarlo ver
> [docs/analisis-java.md](../analisis-java.md).

# Alucal v5.5 — Derivación de cantidades de una abertura

Informe de reverse-engineering del cálculo de materiales (perfiles, accesorios, vidrios) para reimplementarlo en PHP/Laravel. Todas las rutas son relativas a `Alucal_v5.5/` y las citas tienen la forma `_decompiled/<archivo>:<línea>`.

Archivos base: `_decompiled/services/AberturaService.java`, `_decompiled/services/AberturaServiceCorrediza.java`, `_decompiled/services/AberturaServiceBatiente.java`, `_decompiled/services/AberturaServiceMarcoFijo.java`, `_decompiled/services/AberturaServiceProyectante.java`, `_decompiled/services/AberturaServiceGuillotina.java`, `_decompiled/daos/DaoAberturaSimple.java`, `_decompiled/entities/AberturaSimple.java`, `_decompiled/entities/CantBarras.java`, `_decompiled/entities/CantAccesorios.java`, `_decompiled/entities/CantVidrios.java`, `_decompiled/entities/param/*`.

Convenciones observadas: perfiles y accesorios se miden en **mm enteros** (`CantBarras.largo`) y **metros/lineales** (`CantAccesorios.cantidad`); los vidrios se guardan en **metros double** (`CantVidrios.ancho/alto`) y se exportan a mm redondeando (`_decompiled/entities/CantVidrios.java:95-101`).

## 1. Flujo

1. **Entrada**: `ParamGenerales` = `tipo`, `cantidad`, `alto`, `ancho`, `terminacion`, `tipologia`, `serie`, `ubicacion`, `colorHerrajes`, `colorPlasticos` (`_decompiled/entities/param/ParamGenerales.java:12-21`).
2. **Parámetros por defecto**: `AberturaService.getAberturaSimplePorDefecto` (`_decompiled/services/AberturaService.java:89-101`) arma `pp` con `ServiceParamParticulares.getParamParticularesPorDefecto(tipologia, serie, ancho, alto)` y delega.
3. **Resolución de parámetros** (`_decompiled/services/AberturaService.java:123-194`): ver §2.
4. **Dispatch** (`_decompiled/services/AberturaService.java:196-492`) por `pg.getTipologia().getId()` y luego `pg.getSerie().getId()`. IDs de tipología usados: 1 celosía (375), 2 batiente (249), 3 corrediza (200), 4 guillotina (422), 5 mampara (435), 6 marco fijo (272), 7 oscilobatiente (378), 8 parasol (376), 9 proy-deslizante (399), 10 proyectante (305), 11 puerta (322), 12 tabaquera (351), 13 uniblock (441), 14 corrediza 2+1 (449), 15 corrediza 2+2 (456), 20 corrediza superhermética (463), 21 claraboya (470), 22 alzante (475), 23 corrediza con paño fijo (480), 24 corrediza embutido lateral (487). *(Los nombres de tipología/serie viven en la tabla `Tipologia`/`Serie`, no en el código: `_decompiled/daos/DaoTipologia.java:97-110`.)*
5. **Cálculo concreto** (un método por serie, p.ej. `AberturaServiceMarcoFijo.getFijoHA62`, `AberturaServiceCorrediza.getCorredizaS20_2Hojas`, `AberturaServiceBatiente.getBatientePrima_1Hoja_CamaraCompensadora`): lee valores del `Map`, valida `"Faltan parámetros!"`, aplica el bloque Uniblock Mix, crea `AberturaSimple` con tres listas vacías y las va llenando con `barras.add(new CantBarras(...))`, `accesorios.add(new CantAccesorios(...))`, `vidrios.add(new CantVidrios(...))`. Cierra el método típico con `agregarPremarcoYTapajuntas(...)` + `agregarGrampas(...)` (p.ej. `_decompiled/services/AberturaServiceMarcoFijo.java:310-311`).
6. **Post-proceso** (`_decompiled/services/AberturaService.java:493-512`), en este orden: `setId`/`setIdElementoObra`/`setIdRellenoVano`/`setNumero` → `aplicarCorrecciones` → `setEdicionesMateriales` + `aplicarEdicionesDeMateriales` → `compactarLineas` → validación de espesor máximo (`DaoTipologiaSerie.getMaxEspesorVidrioEnMM`), que lanza `DatoInvalidoException` si algún vidrio excede.
7. **`aplicarCorrecciones`** (`_decompiled/services/AberturaService.java:1869-1914`): por cada `CorreccionesDescuento` de (tipología, serie) cuyos parámetros coinciden (`aberturaSimpleCumpleCondiciones`, 1916-1925) suma `correccion` al `largo` de las barras cuya clave `perfil.getNumero()+"@@@"+orientacion` coincide, ajusta **todos** los vidrios (`ancho += correccionAnchoAcristalamientoEnMM/1000`, `alto += correccionAltoAcristalamientoEnMM/1000`, 1894-1897), quita accesorios por `codigo+"@@@"+parte` y agrega los `AGREGAR`.
8. **`compactarLineas`** (`_decompiled/services/AberturaService.java:515-549`): fusiona barras con igual perfil+largo+descripcion+parte+corte+terminacion+orientacion (522), accesorios con igual accesorio+parte (532), vidrios con igual vidrio+altoMM+anchoMM+descripcion (543).
9. **Salida**: tres listas. `CantBarras` = (perfil, cantidad, largo, descripcion, parte, corte, terminacion, orientacion) (`_decompiled/entities/CantBarras.java:14-22`); `CantAccesorios` = (accesorio, cantidad, parte) (`_decompiled/entities/CantAccesorios.java:11-14`); `CantVidrios` = (vidrio, cantidad, alto, ancho, descripcion) (`_decompiled/entities/CantVidrios.java:9-14`).
10. **Orden de líneas**: el service **no ordena**; el orden es el de inserción. Existen comparadores para reportes: barras `PREMARCO < TAPAJUNTAS < MARCO < HOJA < CONECTOR < MOSQUITERO` (`_decompiled/entities/CantBarras.java:137-178`); accesorios `PREMARCO < MARCO < HOJA < CONECTOR < MOSQUITERO < TAPAJUNTAS < UNIBLOCK_MIX` (`_decompiled/entities/CantAccesorios.java:84-153`).
11. **Partes** (`_decompiled/entities/AberturaSimple.java:689-697`): `PREMARCO, TAPAJUNTAS, MARCO, HOJA, MOSQUITERO, CONECTOR, UNIBLOCK_MIX`. **Corte** (`_decompiled/entities/CantBarras.java:229-233`): `CORTE_45_45, CORTE_90_90, CORTE_45_90, CORTE_90_45`. **Orientación**: `HORIZONTAL | VERTICAL`.

## 2. Parámetros

### 2.1 Orden de resolución (`_decompiled/services/AberturaService.java:123-194`)

1. Definiciones aplicables = `DaoDefinicionParametroParticular.getDefinicionParametros(tipologiaId, serieId)` (`_decompiled/services/AberturaService.java:137`).
2. Se buscan los `ParametrosPorDefectoAbertura` de ese par tipología-serie (`126`) y se toma **el primero** cuyas condiciones se cumplen (`131-136`). Condiciones: `ALTO`, `ANCHO`, `ESPESOR_VIDRIO` (contra la lista de espesores de los acristalamientos), `LLEVA_UNIBLOCK_MIX`, `NO_LLEVA_UNIBLOCK_MIX`; la comparación es **estricta** `valor > rango[0] && valor < rango[1]` (`_decompiled/entities/ParametrosPorDefectoAbertura.java:88-119`).
3. Para cada definición: si el registro por defecto la trae, gana ese valor (`140-142`); si no, `def.getUnValorValidoCualquiera(ancho, alto)` (`191`).
4. **Acristalamiento** tiene default programático, no de DB (`144-190`): tipología 14 → `DVH(vidrio 1, cámara 6, vidrio 1)` + `VidrioSinCamara(1)`; `serie.isRpt()` → `DVH(1,6,2)`; serie 38 → `DVH(3,12,3)`; resto → `VidrioSinCamara(1)`.
5. **Ajustes posteriores** por tipología/serie: `ServiceParamParticulares.ajustarParamParticulares` (`_decompiled/services/ServiceParamParticulares.java:124-171`) — Batiente HA62 fuerza contravidrios `RECTOS` si espesor < 14; Puerta Ecothermic Plus fuerza hoja `ADR 85415` si apertura EXTERIOR.
6. En el **camino por defecto del editor** (`_decompiled/services/ServiceParamParticulares.java:35-48`) se usa la misma idea pero el acristalamiento por defecto se arma con `getParamParticularAcristalamientoPorDefecto` (`97-122`), que **siempre** termina poniendo `VidrioSinCamara(1)` (el DVH por RPT se sobreescribe, 99-115).

### 2.2 Claves exactas por tipología (valores del enum `EnumDefParametroParticular`, con su id, en `_decompiled/daos/DaoDefinicionParametroParticular.java:534-693`)

| Tipología | Claves que consume |
|---|---|
| Corrediza (3) S15 | `TIPO_CORREDIZA_S15`(106), `UNIBLOCK_MIX`(7), `ACRISTALAMIENTO_OPCION_DIVISOR_Y_OPCION_TABLILLA`(104), `PREMARCO`(6), `CIERRES_CORREDIZA`(102), `UMBRAL_EMBUTIDO_PUERTA_VENTANA`(168) — `_decompiled/services/AberturaServiceCorrediza.java:6491-6496` |
| Corrediza (3) S20 | `TIPO_CORREDIZA_S20`(154), `PUNZONADO`(38), `ZOCALO_RUEDA_CORREDIZA_S20`(108), `CABEZAL_CORREDIZA_S20`(69), `MARCO_LATERAL_CORR_S20`(70), `ENGANCHE_CENTRAL_S20`(39), `MARCO_INF_CORREDIZA_S20`(123) — `_decompiled/services/AberturaServiceCorrediza.java:6579-6590` |
| Corrediza (3) otras series | `TIPO_CORREDIZA_DOS_GUIAS_*`(49/54/59/61/64/67), `TIPO_CORREDIZA_DOMINA`(121), `TIPO_CORREDIZA_ECOSLIDE`(134), `TIPO_CORREDIZA_NORDICA`(153), `TIPO_CORREDIZA_DOMO`(164), `TIPO_CORREDIZA_HA110_RPT`(172), `TIPO_CORREDIZA_ORBITAL`(177), `TIPO_CORREDIZA_MOSQ_*`(120/176), `MARCO_45_Y_90`(55), `ENGANCHE_CENTRAL_PRIMA_45`(60), `ENGANCHE_CENTRAL_PRIMA_90`(63), `RUEDAS_*`(110/111/112/115), `ZOCALO_ALTO_Y_BAJO`(62), `ZOCALO_S25`(41), `CABEZAL_S25`(42), `PARANTE_LATERAL_S25`(43), `TIPO_ENGANCHE`(45), `ENGANCHE_S25`(46), `TIPO_CORREDIZA_S25`(96), `MARCO_LATERAL_S25`(97), `MARCO_CORREDIZA_ROTONDA_640`(65) |
| Batiente (2) | `CANT_HOJAS_1_o_2`(72), `HOJA_CURVA_RECTA`(18), `CONTRAVIDRIOS_CURVOS_RECTOS`(15), `TIPO_CAMARA`(74), `LLEVA_MOSQUITERO_FIJO`(9), `CIERRE_ALDABILLA_o_FALLEBA`(73), `UNIBLOCK_MIX`, `ACRISTALAMIENTO_ENTERO_CON_OPCION_TABLILLA`(103), `PREMARCO` — `_decompiled/services/AberturaServiceBatiente.java:280-288` |
| Marco Fijo (6) | `MARCO_FIJO_HA62`(25), `MARCO_CURVO_O_RECTO`(28), `MARCO_FIJO_S30`(33), `MARCO_FIJO_ROT640`(30), `MARCO_FIJO_DOPPIA`(156), `MARCO_TAB_Y_FIJO_ECOTHERMIC_PLUS`(14), `MARCO_FIJO_HORIZONTAL_S20`(31)/`_VERTICAL_S20`(36), `MARCO_FIJO_TUBULAR`(34), `MARCO_FIJO_HA62RPT`(35), `DIVISORES_SIN_OPCIONES`(24), `DIVISORES_MARCO_FIJO_PRIMA`(27)/`_ROT640`(29)/`_S30`(32)/`_FORMA`(26), `CONTRAVIDRIOS_CURVOS_RECTOS`(15), `CONTRAVIDRIOS_FIJO_R640`(155), `DEFINIR_CONTRAVIDRIOS_MANUALMENTE`(135), `PORTACONTRAVIDRIOS`(116), `ACRISTALAMIENTO_ENTERO`(5), `UNIBLOCK_MIX`, `PREMARCO` |
| Proyectante (10) S20 | `UNIBLOCK_MIX`, `ACRISTALAMIENTO_ENTERO`, `PREMARCO` (`_decompiled/services/AberturaServiceProyectante.java:226-228`) |
| Corrediza 2+1 (14) / 2+2 (15) | `MARCO_INFERIOR_DOPPIA_2MAS1`(125)/`2MAS2`(133), `MARCO_SUPERIOR_DOPPIA_2MAS1`(126), `PARANTE_LATERAL_DOPPIA`(127), `TIPO_ENGANCHE_DOPPIA`(129), `ZOCALO_DOPPIA`(130), `CABEZAL_DOPPIA`(131), `ACRISTALAMIENTO_2MAS1`(132), `UNIBLOCK_MIX`, `PREMARCO`, `UMBRAL_EMBUTIDO_PUERTA_VENTANA` |
| Corrediza con paño fijo (23) | `CANT_HOJAS_CORREDIZAS_C_PF`(175), `ACRISTALAMIENTO_ENTERO`, `PREMARCO` (`_decompiled/services/AberturaServiceCorredizaPañoFijo.java:156-158`) |

Parámetros transversales: `PREMARCO`, `UNIBLOCK_MIX`, `ACRISTALAMIENTO_*`, `LLEVA_MOSQUITERO_FIJO`(9)/`MOSQUITERO_*`(48/139/141), `UMBRAL`(118)/`UMBRAL_BAJO_ALTO`(162)/`UMBRAL_EMBUTIDO`(169).

### 2.3 Tipos y defaults

`TipoDeDato` (`_decompiled/entities/definicionparametroparticular/DefinicionParametroParticular.java:99-113`): `OPTIONS_MAS_OTRO, OPTIONS, PERFIL, ACRISTALAMIENTO, PREMARCO, UNIBLOCK_MIX, MOSQUITERO, ACCESORIO, DIVISORES, TIPO_CORREDIZA, INTEGER, CIERRES_CORREDIZA, CONTRAVIDRIOS`. Categorías (`116-133`): `CANT_HOJAS, PERFILES, ACRISTALAMIENTO, ACCESORIOS, UNIBLOCK_MIX, PREMARCO`.

- **OPTIONS**: default = `opciones.get(0)` (`_decompiled/entities/definicionparametroparticular/DefinicionParametroParticularOptions.java:52-54`). No hay default declarativo en código: el orden de opciones viene de la DB.
- **PERFIL / ACCESORIO / MOSQUITERO / DIVISORES / TIPO_CORREDIZA / CIERRES / CONTRAVIDRIOS**: default = primera fila válida que devuelve su `getUnValorValidoCualquiera`.
- **PREMARCO**: luces por defecto `4, 4, 0, 4` (sup, der, inf, izq) — `_decompiled/entities/param/ParamParticulares.java:110`. Si `tipoPremarco == POR_DEFECTO`, el código sale del perfil de marco (`getCodigoPremarcoPorDefecto()`) y si no existe cae en `"4570"` (`_decompiled/services/AberturaService.java:566-576`); el tapajuntas cae en `"4574"` (`591-593`).
- **UNIBLOCK_MIX** (`_decompiled/entities/definicionparametroparticular/ValorParametroParticularUniblockMix.java:20-30`): `cortina = ALUMINIO|PVC`; `cajon = ALUMINIO_185 | ALUMINIO_185_COPLANAR(185) | PVC_155(155) | PVC_185(185)` (`133-147`); `medidas = ABERTURA|VANO`; `jamba = 1494|1495|1496`.
- **ACRISTALAMIENTO** (`_decompiled/entities/Acristalamiento.java:443-454`): `TipoAcristalamiento = ACRISTALAMIENTO_ENTERO | DIVISOR_AL_MEDIO`; `MaterialAcristalamiento = VIDRIO | TABLILLA | COLONIAL`. Espesor: vidrio → `Vidrio.getEspesorEnMM()`; colonial → 4; tablilla → tabla fija por código (`296-331`).

## 3. Fórmulas por tipología

### 3.1 Marco Fijo (6)

Marco genérico (2 horizontales + 2 verticales, 45-45, largo = medida nominal sin descuento): `_decompiled/services/AberturaServiceMarcoFijo.java:73-74`, `122-123`, `202-203`, `295-296`, `532-533`, `627-628`.

Contravidrios + divisores + vidrios se centralizan en `AberturaService.agregarAcristalamientoBurletesEscuadrasDivisoresYContravidriosMarcoFijo` (`_decompiled/services/AberturaService.java:2391-2449`):

```
cantContravidriosHorizontales = (1 + cantDivisoresHorizontales) * 2 * (1 + cantDivisoresVerticales)      // 2403
cantContravidriosVerticales   = (1 + cantDivisoresVerticales) * 2 * (1 + cantDivisoresHorizontales)      // 2404
largoContravidriosVerticales  = (largoContravidriosVerticalesEnteros - anchoDivisorHorizontal*cantDivHor) / (cantDivHor+1)   // 2406
// corte 45-45:
largoContravidriosHorizontales = (largoContravidriosHorizontalesEnteros - anchoDivisorVertical*cantDivVer) / (cantDivVer+1)   // 2410
// corte 90-90 (resta además 2 contravidrios verticales por paño):
largoContravidriosHorizontales = (largoContravidriosHorizontalesEnteros - anchoDivisorVertical*cantDivVer
                                  - anchoContravidrioVertical*(cantDivVer+1)*2) / (cantDivVer+1)          // 2414
largoDivisoresHorizontales = (largoContravidriosHorizontalesEnteros - anchoDivisorVertical*cantDivVer) / (cantDivVer+1) // 2421
cantVidrios = (cantDivHor+1)*(cantDivVer+1)                                                              // 2427
anchoVidrios = round((anchoVidrio - (anchoDivisorVertical+6.0)*cantDivVer/1000.0)/(cantDivVer+1)*1000)/1000  // 2428
altoVidrios  = round((altoVidrio  - (anchoDivisorHorizontal+6.0)*cantDivHor/1000.0)/(cantDivHor+1)*1000)/1000 // 2429
perimVidrios = (anchoVidrios + altoVidrios) * 2.0                                                        // 2431
```

Burletes: si burlete exterior == interior → `perimVidrios*cantVidrios*2.0`; si difieren → cada uno `perimVidrios*cantVidrios`; si sólo hay uno → `perimVidrios*cantVidrios` (`2432-2445`). Escuadras de divisor: `(cantDivHor+cantDivVer)*2 + cantDivHor*cantDivVer*2` (`2447`).

Variantes de contravidrio por serie (todas hardcodeadas):

- HA62: `largoContHorEnteros = (anchoM - 0.044 - descuentoExtra/1000)*1000`, ídem vertical; `descuentoExtra = 38` si el marco es `ADR 1474` (`_decompiled/services/AberturaServiceMarcoFijo.java:299-308`).
- Ecothermic Plus: `descuentoVidrio` según marco (`ADR 85401`→0.068, `ADR 85402`→0.076, `ADR 85403`→0.096; `124-133`); si contravidrios RECTOS resta 38 mm al horizontal (`154`).
- Prima: contravidrio por espesor y curvatura (`370`), divisores `6221`=24 mm / `6222`=34 mm (`384-403`).
- PrimaPlus: contravidrios `ancho-58`/`alto-58`; curvo → dif. 24, recto → dif. 12 (`455-466`).
- S20: `altoContravidrios = alto-34|-44|-29`, `anchoContravidrios = ancho-34-24|-44-24|-29-24` según marcos 206/209/241 (`706-711`); espesor permitido ≤ 6 mm (`713-718`).
- Rotonda640: `ancho-49/alto-49` (ADR 1161) o `-59` (ADR 1512/2466) y divisor propio (`559-579`).

### 3.2 Corrediza (3)

**S15** (`_decompiled/services/AberturaServiceCorrediza.java:6538-6564`) — sólo 2 hojas, vidrio 4 mm:

```
anchoMarco = ancho - 21;  altoHoja = alto - 35;  anchoZocalo = ancho/2 - 26
anchoVidrioEnMetros = anchoZocalo/1000.0 - 0.007
altoVidrioEnMetros  = altoHoja/1000.0  - 0.023
// marco: 1500 (1×anchoMarco, H, 90-90), 1501 (1×anchoMarco, H), 1502 (2×alto, V)
// hoja: 1504 cabezal (2×anchoZocalo), 1508 zócalo (2×anchoZocalo), 1505 lateral (2×altoHoja), 1506 enganche (2×altoHoja)
```

**S20, 2 hojas** (`6676-6812`):

```
marcoLateral "205" → anchoMarco = ancho - 22; baseMarcoLateral = 10
marcoLateral "235"|"291" → anchoMarco = ancho; baseMarcoLateral = 0;
    altoMarco = cajaDeAgua ? alto-23-15 : alto-23
cajaDeAgua → altoHoja = alto-63; altoHojaMosquitero = alto-32
si no       → altoHoja = alto-48; mosquitero alto-33 (marco inf 204) / alto-37 (236)
altoVidrioEnMetros = altoHoja/1000.0 - 0.068 ; -= 0.016 si zócalo o cabezal ∈ {2210,2211}
enganche "203": altoEnganche = alto-85; anchoZocaloManual = (ancho-148-2*baseMarcoLateral)/2
                anchoVidrioEnMetros = ((ancho-148-2*baseMarcoLateral)/2 + 14)/1000
resto:          altoEnganche = alto-48; anchoZocaloManual = (ancho-126-2*baseMarcoLateral)/2
                anchoVidrioEnMetros = ((ancho-128-2*baseMarcoLateral)/2 + 14)/1000
anchoZocalo = punzonado=="MANUAL" ? anchoZocaloManual : anchoZocaloManual + 63
anchoHojaMosquitero = anchoZocaloManual + 87
```

**S20, 3 hojas** (`6933-6952`):

```
conEncuentroATope:  anchoZocaloLateralManual = enganche=="203" ? (ancho-220-2*base)/3 : (ancho-203-2*base)/3
hojaDelMedioMasGrande: lateral = (ancho-272-2*base)/4 | (ancho-236-2*base)/4 ; central = lateral*2 + 77
resto:              lateral = (ancho-195-2*base)/3 | (ancho-158-2*base)/3 ; central = lateral
anchoVidrioLateral = (anchoZocaloLateralManual + 14)/1000 ; central = (anchoZocaloCentralManual + 14)/1000
```

En 3 hojas con `hojaDelMedioMasGrande` el vidrio se emite como 2 laterales + 1 central (`7027-7029`); si no, `3` hojas iguales (`7031`). Con `DIVISOR_AL_MEDIO` se agrega divisor y el vidrio se parte: `altoMedioVidrio = (altoVidrioEnMetros - 16/1000.0)/2.0` (`6802-6804`, `7034-7035`).

**Ancho de hoja S25** (`_decompiled/services/AberturaService.java:1449-1504`, versión completa):

```
baseMarcoLateral: "2501"|"2534" → 25.5 ; "2515" → 9.0 ; "2518" → 1.5 ; "4565" → 28.5
pieDeParante:     hojaLateral "2544"|"4505"|"4506" → 13.8 ; "255"|"266" → 16.5
anchoEnganche:    enganche "4507"|"4537"|"4538" → 36.0 ; "257"|"258"|"269" → 58.0
encuentroATope:   "2509" → 15.0 ; "2549" → 2*pieDeParante + 4.0
cantHojas==2 → (ancho - 2*baseMarcoLateral - 6.4 - 2*pieDeParante + anchoEnganche)/2
cantHojas==3 → conEncuentroATope ? (... + anchoEnganche - encuentroATope)/3 : (... + 2*anchoEnganche)/3
cantHojas==4, cantGuias==2 → (... + 2*anchoEnganche - encuentroATope)/4
cantHojas==4, cantGuias==4 → (ancho - 2*baseMarcoLateral - 26.8 - 2*pieDeParante + 3*anchoEnganche)/4
punzonadoManual → retorno -= (hojaLateral.length()==3 ? 114.0 : 72.0)   // 1502
```

(Existe una sobrecarga previa, `1402-1443`, con `-74.0` fijo para punzonado manual y sin hoja plus.)

**Rodamiento** por peso de hoja: `getPesoHoja` = `pesoVidrio + pesoPerfiles*1.15` (`13854-13855`), con `pesoVidrio = ancho*alto*espesor*2.5 kg/m²/mm` (`13880-13891`; DVH suma espesores exterior+interior) y `pesoPerfiles` = 2 barras horizontales + 2 verticales más pesadas de la parte HOJA (`13859-13878`); luego `getCodigoRodamiento(serie, pesoHoja, zocalo, rueda)` elige por tramos (`13921+`).

### 3.3 Batiente (2)

1 hoja (`_decompiled/services/AberturaServiceBatiente.java:382-426`):

```
hojaHorizontal = ancho - 39 ; hojaVertical = alto - 39
marco 6210: 2×(ancho, H, 45-45) + 2×(alto, V, 45-45)
hoja (RECTA 6211 / CURVA 6234): 2×hojaHorizontal + 2×hojaVertical, 45-45
contravidrio = f(hoja, tipo, tieneDVH):  CURVOS → DVH? 6232 : 6257 | 6231 : 6230
                                        RECTOS → DVH? 6225 : 6212 | 6226 : 6217
acristalamiento: alto = (hojaVertical-92)/1000 ; ancho = (hojaHorizontal-92)/1000 ; cantHojas = 1
```

2 hojas (`596-636`):

```
hojaVertical = alto - 39 ; hojaHorizontal = ancho/2 - 23 ; encuentro central "6223" 1×(alto-104)
hoja y contravidrio: cantidad 4 ; vidrio: (hojaVertical-92)/1000 × (hojaHorizontal-92)/1000 × 2
```

Accesorios: bisagras 2 (1 hoja) / 4 (2 hojas) `230177`→color (`410`, `616`); `230049` = `round(ancho/500)`; felpilla `230630` = `(anchoM+altoM)*2`; burletes variables (`230627` = `(anchoM+altoM)*2` en 1 hoja, `anchoM*2+altoM*4` en 2 hojas); cierre según `FALLEBA`/`ALDABILLA` con códigos por color de herrajes (`349-358`, `415-421`).

### 3.4 Accesorios genéricos y burletes

`AberturaService.getCantidad(tipoAcc, ancho, alto, cantHojas, cantGuias, cantDivHor, cantDivVer)` (`_decompiled/services/AberturaService.java:1519-1611`):

```
case 0 (BURLETE_U):   anchoHoja = ancho/cantHojas ; round(anchoHoja/0.5)  (2 y 1 hojas) ; 3 hojas → round(anchoHoja*2/0.5)
case 1:               2 * BURLETE_U
case 2:               2 hojas → ancho + alto*2 ; 3 hojas → ancho*2/3 + alto*2
case 3:               alto*2 (2 y 3 hojas)
case 4:               1 hoja → ancho*2+alto*2+cantDivHor*ancho*2+cantDivVer*alto*2 ; 2 hojas → ancho*2+alto*4+cantDivHor*ancho*2 ; 3 hojas → ancho*2+alto*6
case 6:               2 hojas → ancho*4+alto*6 ; 3 hojas → ancho*4+alto*8
case 8:               2 hojas → ancho/2*alto ; 3 hojas → ancho/3*alto
case 4 hojas → UnsupportedOperationException("Falta implementar")
```

Burletes Batiente/Oscilo/Proyectante/Tabaquera/ProyDesliz Prima: `cantidad = (ancho*2 + alto*2*cantHojas)/1000.0` y código según espesor (`607-637`).

### 3.5 Premarco y tapajuntas

`agregarPremarco` (`_decompiled/services/AberturaService.java:2552-2665`): tabla `sumarAlPremarco` por código (`1485`→26, `2195`→35, `4570/4571/4577/4579`→32, `4588`→51, `6066/6079/6205/6901/7227`→30, `ADR 230/761`→43, `ADR 2461/2548`→45, `18252`→0). Con umbral:

```
2 × (ancho + sumarAlPremarco + luzHorizontalEnMM)  [H, 45-45, PREMARCO, SIN_ANODIZAR]
2 × (alto  + sumarAlPremarco + luzVerticalEnMM)    [V, 45-45]
escuadra: cantEscuadrasPorEsquina * 4  (o *2 si no lleva abajo, 2655-2661)
```

Sin umbral: dintel 1×ancho, jambas 2×`(alto + sumarAlPremarco/2 + luzVertical)`, corte `CORTE_45_90`.

`agregarTapajuntas` (`2667-2819`): `descuento` por código (`ADR 62`→66, `ADR 191/561`→87, `ADR 1164`→34, `ADR 1298`→86, `ADR 1297`→84, `ADR 1662`→67, `ADR 1517`→50, `ADR 2462`→-3, `4574`→6 (22 si premarco `7227`), `6206`→53, `7211`→58, `16002`→45, `18232`→10, `18662`→68). Con tapa abajo: 2×`(ancho+descuento)` + 2×`(alto+descuento)`, `perimetroEnMM = (ancho+alto)*2`, 4 escuadras. Sin tapa abajo: 1×`(ancho+descuento)` + 2×`(alto+descuento/2)`, `perimetro = ancho+alto*2`, 2 escuadras. Clips: `ceil(perimetroEnMM/300.0)` (`2804`). `sumarALaAbertura=false` (4574, 18232, ADR 2462) usa las medidas del premarco.

## 4. Persistencia

**Abertura simple** — se escribe en este orden (`_decompiled/daos/DaoElementoObra.java:128-152`):

1. `INSERT INTO ElementoObra (tipoElemento, idObra, numero)` → `idElemento`; `tipoElemento = ABERTURA_SIMPLE | ABERTURA_COMPUESTA | ESTRUCTURA_ALUMINIO` (`117-127`).
2. `INSERT INTO aberturasimple (id?, cantidad, ancho, alto, idTipologia, idSerie, terminacion, numero, ubicacion, colorHerrajes, colorPlasticos, tipo, idElementoObra, idRellenoVano)` (`_decompiled/daos/DaoAberturaSimple.java:91-105`); el id se omite si ≤ 0 (`87-90`).
3. Por cada valor del `mapParametros`: `INSERT INTO ValorParametroParticular (idAberturaSimple, idParamPorDefecto, idCorreccionDescuentos, idOrdenVerificada, idDefParamParticular)` (`_decompiled/daos/DaoValorParametroParticular.java:752`) + una fila en la tabla del subtipo con el mismo `id`: `ValorParametroParticularOptions(id, option)` (801), `...Premarco(id, llevaPremarco, llevaTapajuntas, tipoPremarco, tipoTapajuntas, codPremarco, codTapajuntas, luzSupEnMM, luzDerEnMM, luzInfEnMM, luzIzqEnMM)` (809), `...Perfil(id, codigoPerfil)` (848), `...OptionsMasOtro(id, option, idValorOtro)` (857), `...UniblockMix(id, conCortina, materialCortina, materialCajon, altoCajon, sonMedidasVano, jamba)` (866), `...Mosquitero` (887), `...Accesorio` (896), `...Acristalamiento` (29 columnas, 905), `...Divisores(id, cantDivisoresHorizontales, divisorHorizontal, cantDivisoresVerticales, divisorVertical)` (998), `...TipoCorrediza(id, tipoCorrediza, mosquitero, encuentroATope)` (1017), `...CierresCorrediza` (1035), `...Integer` (1053), `...Contravidrios` (1061).
4. Ediciones de materiales: `EdicionDePerfiles`, `EdicionDeAccesorios`, `EdicionDeVidrios` (`_decompiled/daos/DaoAberturaSimple.java:263-315`); el vidrio se serializa como `VIDRIOSINCAMARA@<id>` o `DVH@<idExt>@<camara>@<idInt>` (`297-304`).

**Importante**: las cantidades **no** se persisten para una abertura simple. Al leer, `getAberturasSimples` reconstruye y **recalcula** llamando otra vez a `AberturaService.getAberturaSimple` (`_decompiled/daos/DaoAberturaSimple.java:223`). `updateAberturaSimple` = `delete` + `insert` (`258-261`).

**Estructura de aluminio** (u otros elementos que sí guardan cantidades) (`_decompiled/daos/DaoEstructuraAluminio.java:59-90`):

1. `INSERT INTO EstructuraAluminio (id?, numero, ancho, alto, descripcion, cantidad, tipo, ubicacion, dibujo, idElementoObra)` → `id`.
2. `INSERT INTO CantBarras (idEstructura, idValorParametroParticular, perfil, cantidad, largoEnMM, descripcion, parte, corte, terminacion, orientacion)` (`_decompiled/daos/DaoCantBarras.java:41`), con `idValorParametroParticular = -1` cuando viene de estructura (`_decompiled/daos/DaoEstructuraAluminio.java:87`).
3. `INSERT INTO CantAccesorios (idEstructura, idValorParametroParticular, accesorio, cantidad, parte)` (`_decompiled/daos/DaoCantAccesorios.java:39`).
4. `INSERT INTO CantVidrios (idEstructura, cantidad, anchoEnMetros, altoEnMetros, idVidrioSinCamara, idVidrioExterior, espesorCamaraEnMM, idVidrioInterior, descripcion)` (`_decompiled/daos/DaoCantVidrios.java:21-38`; los no usados van en `-1`).

**Relación**: `ElementoObra 1—1 AberturaSimple` (por `idElementoObra`) y `EstructuraAluminio 1—N CantBarras/CantAccesorios/CantVidrios` (por `idEstructura`); `idValorParametroParticular` vincula una cantidad a un parámetro "agregar perfiles" (`_decompiled/daos/DaoCantBarras.java:74-79`). El borrado es en cascada manual: primero `CantBarras`/`CantAccesorios`/`CantVidrios` (`_decompiled/daos/DaoEstructuraAluminio.java:50-56`), y para aberturas `EdicionDe*` + `ValorParametroParticular*` antes de `aberturasimple` (`_decompiled/daos/DaoAberturaSimple.java:57-64`).

## 5. Particularidades

- **Corrediza 2+1 (tipología 14, serie 3 "Doppia")**: 2 hojas corredizas + 1 paño fijo lateral. Jambas distintas `ADR 2472` (lado fijo) y `ADR 2471` (lado móvil), ambas `1×alto`; cubrehoja `ADR 2477` 1×`(ancho-48)`; refuerzo de umbral `ADR 1152` (2×) si marco inferior `ADR 2496`, si no `ADR 1661` en `SIN_ANODIZAR` (`_decompiled/services/AberturaServiceCorrediza.java:12902-12911`). Hojas: `alto - descuentoAltoHoja` con `descuentoAltoHoja = 70` (+45 por cada marco `ADR 2496`, `12912-12920`); horizontales de hoja `ancho/2 - 16 + posRelativaEngCentralMedio` (`12922-12923`). Vidrios: 1 fijo con `Acristalamiento` **2** (`getAcristalamiento2()`) y 2 corredizas con el 1 (`12984-12992`).
- **Corrediza 2+2 (tipología 15, serie 3)**: 4 hojas (2 corredizas + 2 fijas), `descuentoHojaVertical = 70` (`13044`), parantes 6× / cruce doble `ADR 2480` 2×, zócalo y cabezal 8×`(ancho/2-16)` o 4+4 según `ADR 2204` (`13054-13076`), **un solo vidrio con cantidad 4** `anchoM/2.0 - 0.1` (`13105`).
- **Paño fijo en corrediza (tipología 23, serie 50 "M2 1+1")**: sólo 1 hoja corrediza + 1 fijo (valida y lanza excepción si no, `_decompiled/services/AberturaServiceCorredizaPañoFijo.java:206-208`). `aMenos43 = ancho-43` para dintel/umbral; hojas `ancho/2-17`, `ancho-106`, `ancho-32`, `ancho-48` (`209-228`); vidrios: hoja móvil `alto/1000-0.23` × `(ancho/2-25)/1000-0.053`, paño fijo `alto/1000-0.052` × `(ancho/1000/2) - 0.019` (1 hoja) o `-0.043` (`184-189`). Felpilla `4*anchoM+6*altoM`, cuña `2*(anchoM+altoM)` (`239-240`).
- **DVH**: cambia la selección de contravidrio (`tieneDVH()`, `_decompiled/entities/Acristalamiento.java:430-441`) y el peso (`13883-13888`); para series RPT el acristalamiento por defecto es DVH(1,6,2) (`_decompiled/services/AberturaService.java:159-168`). `FijoEcothermicPlus` **exige** DVH (`_decompiled/services/AberturaServiceMarcoFijo.java:99-101`).
- **Mosquitero**: no es un parámetro booleano suelto sino que viene del `EnumTipoCorrediza` (`llevaMosquitero`, `cantHojasMosquitero`, `mosqUsaGuiaDeHoja`, `hojaDelMedioMasGrande`, `llevaEncuentroATope`, `cantEnganches`) — `_decompiled/entities/EnumTipoCorrediza.java:10-56`. Agrega guías `213` (`cantGuiasMosquitero` = 2, o 1 si marco inferior `236`), hojas `214` 45-45 y un juego de accesorios (`_decompiled/services/AberturaServiceCorrediza.java:6783-6796`); el tejido se elige por `min(ancho,alto)` con códigos `230861…232001` y cantidad = `max/1000 * cantHojas` (`14138-14157`). En batiente/proyectante el mosquitero es fijo: 2×`(ancho-5)` + 2×`(alto-5)` en 45-45, escuadra `230661` (perfil 214) o `230428` (6255) (`_decompiled/services/AberturaService.java:3179-3203`).
- **Premarco/tapajuntas**: siempre se agregan al final del método de la serie, con `llevaPremarcoAbajo=true, llevaTapajuntasAbajo=true` en la mayoría (`p.ej. _decompiled/services/AberturaServiceMarcoFijo.java:310`). El premarco va con terminación `SIN_ANODIZAR` y `EnumTerminacion2` no se propaga luego (`AberturaSimple.setTerminacion` saltea PREMARCO y SIN_ANODIZAR, `_decompiled/entities/AberturaSimple.java:457-464`).
- **Uniblock Mix**: si `medidas == VANO` entonces `ancho -= 80` y `alto -= cajon.getAltoCajon()` en **todas** las tipologías (`_decompiled/services/AberturaServiceMarcoFijo.java:61-69`, `_decompiled/services/AberturaServiceBatiente.java:367-376`, `_decompiled/services/AberturaServiceCorrediza.java:6526-6535`, etc.); se valida `alto > 1500 && altoCajon < 180` → excepción. Agrega accesorios de cortina por material de cajón y cortina (`_decompiled/services/AberturaService.java:1253-1311`), doble guía `18468` 1×alto si el ancho supera 1800, y 2 jambas (`1494/1495/1496`) de `alto` (`1303`). En el modelo, `getCajonesArmados` vuelve a sumar 80 y el alto de cajón cuando `EnumMedidas.ABERTURA` (`_decompiled/entities/AberturaSimple.java:665-687`) — es decir, el signo del ajuste se invierte en ese método.
- **Fachadas vidriadas**: no pasan por `AberturaSimple`. `FachadaVidriadaService` tiene tres entradas — `getPielDeVidrio(ParametrosPielDeVidrio)`, `getFrenteVidriado(ParametrosFrenteVidriado)`, `getFrame(ParametrosFrame)` — que devuelven `Object[]` con listas agregadas en `Map<String,CantBarras>` / `Map<String,CantAccesorios>` (agrupando por código) (`_decompiled/services/FachadaVidriadaService.java:29-30, 227-236, 348`). Se parametriza por listas de anchos y altos, matriz `boolean[][] ventanas`, códigos de columna/tapa vertical y horizontal, `cantAnclajesPorColumnaVertical`, `cantAcoplesA90`; `cantColumnasVerticales = anchos.size()+1+cantAcoplesA90`, `cantColumnasHorizontales = altos.size()+1`, `anchoColumna = 60` (`237-240`); suma suplemento DVH `ADR 1455` cuando el vidrio es DVH (`241-243`).

## 6. Ambigüedades

1. **Nombres de tipología y serie**: el código sólo usa IDs; los nombres están en las tablas `Tipologia` (`_decompiled/daos/DaoTipologia.java:97-110`) y `Serie`. No puedo afirmar a qué serie comercial corresponde cada ID (18, 19, 31, 32, 41, 42, 49, …) sin la DB.
2. **Universo de parámetros por par tipología-serie**: `getDefinicionParametros(tipologia, serie)` sale de la DB; el enum `EnumDefParametroParticular` sólo fija IDs y nombres de claves. No se puede reconstruir qué claves aplican a cada combinación sin datos.
3. **Constantes de corte**: no hay tabla de descuentos; están hardcodeadas por serie y por variante de perfil dentro de cada método. `_decompiled/services/AberturaServiceCorrediza.java` tiene 15.542 líneas con ~29 métodos de entrada y decenas de variantes (2/3/4 hojas, con/sin encuentro a tope, hoja del medio más grande, zócalos, cabezales, marcos laterales). Transcribir todas excede este informe: sólo están literalizadas S15, S20 (2/3 hojas), el ancho de hoja S25 y las variantes Doppia 2+1/2+2.
4. **Guillotina (4), Proyectante (10) salvo S20, Puerta (11), Tabaquera (12), Oscilobatiente (7), ProyDesliz (9), Uniblock (13), Alzante (22), Claraboya (21), Mampara (5), Parasol (8), Celosía (1)**: no fueron transcriptas en detalle; el patrón es el mismo (marco + hoja + contravidrios + burletes + premarco), pero los descuentos son distintos y no los verifiqué.
5. **`getFijoDoppia` parece incompleto**: devuelve sólo 2 barras horizontales + 2 verticales de marco, sin contravidrios ni vidrios, pese a exigir `Acristalamiento`, `Divisores`, `Premarco` y `UniblockMix` (`_decompiled/services/AberturaServiceMarcoFijo.java:38-80`). No se puede saber si es intencional o un bug.
6. **Caminos no implementados**: `getCantidad` lanza `UnsupportedOperationException("Falta implementar")` para 4 hojas (`_decompiled/services/AberturaService.java:1532, 1547, 1559, 1574, 1589, 1604`); algunas ramas del dispatch no cubren todas las series y, si no hay match, `retorno` queda `null` y `retorno.setId(id)` produce NPE (`_decompiled/services/AberturaService.java:493`).
7. **Orden final de las líneas**: el service no ordena; ignoro si la UI/reportes ordenan o filtran antes de mostrar (los `compareTo` existen pero no se invocan en el cálculo).
8. **Redondeo**: los largos de barra se truncan a `int` por casteo en muchos lugares (`(int)((anchoM - 0.044)*1000.0)`) y en otros se usa `Math.round` (`_decompiled/services/AberturaService.java:2355-2356`). Los vidrios se calculan en metros `double` y sólo se redondean al exportar a mm (`_decompiled/entities/CantVidrios.java:95-101`). No hay una política única: hay que replicar caso por caso.
9. **Unidades de accesorios**: `CantAccesorios.cantidad` es `double` y su significado depende del accesorio (unidades, metros lineales, m², gramos — existe el enum `Unidad` en `_decompiled/entities/CantAccesorios.java:155-159` pero no se usa en el cálculo). No se puede inferir la unidad desde la cantidad.
10. **Correcciones y ediciones**: dependen de tablas (`CorreccionesDescuento`, `CorreccionDescuentoPerfil`, `CorreccionAccesorio`, `EdicionDe*`) cuyo contenido no está en el código; el efecto exacto sobre un presupuesto real no es determinable sin datos. Tampoco sé si hay más de una `CorreccionesDescuento` aplicable a la vez (el código las acumula, `_decompiled/services/AberturaService.java:1874-1898`).
11. **Precios y pesos**: `Perfil.getPeso()` y `Accesorio.getPrecioUnitario()` se leen de la DB; el cálculo de costo queda fuera de este informe.
