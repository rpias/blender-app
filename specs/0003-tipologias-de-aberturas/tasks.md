# Tareas 0003 — Tipologías de aberturas

## Infraestructura

- [x] `Constantes` con lectura de `constantes_calculo` y `perfil_medidas`
- [x] `LineasAbertura` como DTO de salida
- [x] `SerieSinConstantes` con mensajes accionables
- [x] Snapshot en `abertura_simple` (`parametros`, `lineas`, `precioUnitario`, `calculadoEn`)
- [x] `LectorParametros` + `MapeadorParametros` para el histórico
- [x] Comando `alucal:recalcular` con `--dry` y `--obra`

## Calculadoras

- [x] Marco fijo (HA 62, Serie 20, Rotonda 640)
- [x] Corrediza Domina 2 hojas
- [x] Corrediza HA 62 2 hojas (45° y 90°)
- [x] Corrediza Serie 20 2 hojas
- [x] Corrediza Serie 25 2 hojas (con DVH)
- [x] Formularios en la obra para las 4 familias

## Soporte de datos

- [x] DVH en el motor de precios
- [x] Archivo y limpieza de aberturas huérfanas
- [x] Recalcular las 8 aberturas visibles (7 obras)

## Pendiente

- [ ] Series restantes: Domina 3/4 hojas, HA 62 3 hojas, Serie 15, Serie 25 3/4 hojas,
      2+1, 2+2, paño fijo, batiente, proyectante, guillotina, tabaquera, uniblock
- [ ] Accesorios opcionales: mosquitero, umbral embutido, premarco/tapajuntas por tipología
- [ ] Aberturas compuestas (`abertura_compuesta_2`, vano dividido) y estructuras de aluminio
- [ ] Formulario propio para Serie 25
