# Tareas 0007 — Más tipologías

## Batiente (2)

- [x] Portar `getBatientePrima_1Hoja` (marco 6210, hoja 6211/6234, contravidrio por tipo y DVH)
- [x] Portar `getBatientePrima_2Hojas` (encuentro central 6223, hojas y contravidrios ×4)
- [x] Bisagras, falleba/aldabilla y burletes por color de herrajes y espesor
- [x] Suplementos y vidrio `(hoja − 92)` por hoja
- [x] Formulario en la obra (serie, hojas, hoja, contravidrios, cierre, color)
- [x] 4 tests (1 hoja, 2 hojas con DVH, cantidad, error de hoja)

## Corrediza 2+1 Doppia (14)

- [x] Portar `getCorrediza_2_mas_1_Doppia` (marco inferior/superior, jambas 2471/2472, cubrehoja 2477)
- [x] Refuerzos 1152/1661 según umbral alto o bajo
- [x] Enganche simple/reforzado/doble y cierre por color de herrajes
- [x] Dos vidrios: paño fijo y dos hojas móviles
- [x] Formulario en la obra y 3 tests

## Corrediza 2+2 Doppia (15)

- [x] Portar `getCorrediza_2_mas_2_Doppia` (dintel 2495, jambas 2478, cruce doble 2480)
- [x] Parantes simple (6) o mixto (2 + 4 reforzados) y zócalo 2204/2209
- [x] Accesorios 192510/192515/192535/AA808 y vidrio de 4 hojas
- [x] Una sola tarjeta con selector de variante 2+1 / 2+2

## Corrediza + Paño Fijo Nórdical (23)

- [x] Portar `getCorrediza_Nordical` (marco 4251/4252, jambas 3913/4253, hojas 3942-3945)
- [x] Adaptadores ADR 4237 con vidrio simple (se omiten si el perfil no está en el catálogo)
- [x] Accesorios AA710/AA708/AA711/AA498/AA213 y dos vidrios (hoja y paño fijo)
- [x] Formulario y 2 tests

## Proyectante HA 62 (10)

- [x] Portar `getProyDeslizHA62` (marco 1483 y hoja 1484 a 45°, contravidrios −164/−202)
- [x] Contravidrios rectos 1655/1295/916/919/1489/1163/913/910 y curvos 917/918, 920/921, 1174/1175, 914/915, 911/912
- [x] Burletes 193760 + 230711/230710/230714/230807 según espesor
- [x] Formulario y 2 tests

## Guillotina Serie 20 (4)

- [x] Portar `getGuillotinaS20` (marco 204/205, hojas 201/202, enganche 216)
- [x] Punzonado manual vs automático (`alto/2 − 74` vs `alto/2 − 9`)
- [x] Cierre camarón por color (230066 negro / 230067 gris) y burletes 230941/100104
- [x] Formulario y 2 tests
- [x] Verificado en producción: guillotina 1200×1000 → U$S 161

## Formulario único

- [x] Reemplazar las 9 tarjetas por un formulario con selector de variante (`tipologia:serie`)
- [x] Grupos de campos por tipología que se muestran u ocultan según la variante
- [x] El controlador acepta `variante` y deriva tipología y serie
- [x] Test de render y de alta indicando sólo la variante

## Tabaquera HA 62 (12)

- [x] Portar `getTabaqueraHA62` (marco ADR 1470, hoja ADR 1471 a −52, contravidrios −153/−191)
- [x] Cierre sapito (230281/230282 + limitadores H-87), simplón Newton 100460 y tradicional 100462
- [x] Accesorios 230419, 230376, 230716, AA258, 230353, 230728 y burletes
- [x] Formulario y 2 tests · producción: 700×500 → U$S 191

## Pendiente

- [ ] Mosquitero fijo y uniblock mix en batiente
- [ ] Variante doble contacto (`getBatientePrima_1Hoja_DobleContacto`)
- [ ] Corrediza 2+1 y 2+2
- [ ] Paño fijo en corrediza
- [ ] Guillotina en otras series (Prima 2, Rotonda 700, Serie 25, Rotonda 640)
- [ ] Proyectante en otras series (Prima, Serie 20, Serie 30, Forma, Rotonda 640)
- [ ] Oscilobatiente (el kit depende de peso y ancho de hoja: `getCodigoKitOscilobatiente`)
- [ ] Aberturas compuestas (`AberturaCompuesta_2`)
