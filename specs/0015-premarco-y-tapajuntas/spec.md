# Spec 0015 — Premarco y tapajuntas

**Estado:** implementada
**Depende de:** 0002, 0003

## Problema

El motor de precios **ya cobraba** premarco y tapajuntas por metro lineal y los catálogos de precio
estaban importados (18 y 15 perfiles), pero **no había forma de elegirlos** en el formulario: siempre
se calculaban sin ellos. El taller los cotizaba de memoria y el presupuesto no los reflejaba.

## Alcance

- Elegir premarco y tapajuntas en el alta de cualquier abertura, o dejarlos vacíos.
- Decidir si llevan el lado de abajo (perímetro completo) o sólo ancho y verticales.
- Cobro por metro lineal con el precio del catálogo.

Fuera de alcance: premarco y tapajuntas propios de un vano compuesto, escuadras de premarco y el
detalle de barras de premarco en el cómputo.

## Requisitos

| ID | Requisito |
|---|---|
| R1 | El formulario permite elegir un premarco del catálogo o “sin premarco”. |
| R2 | El formulario permite elegir un tapajuntas del catálogo o “sin tapajuntas”. |
| R3 | Se puede indicar si llevan el lado de abajo. |
| R4 | El precio suma metros × precio del catálogo: con abajo 2×(ancho+alto); sin abajo ancho + 2×alto. |
| R5 | La elección queda guardada en los parámetros de la abertura. |
| R6 | Un código inexistente se rechaza con error de validación. |

## Criterios de aceptación

- **R4** — Abertura de 1.000 × 1.000 con premarco 4570 ($10/m): +$40 con abajo y +$30 sin abajo.
- **R4** — Agregando tapajuntas ADR 62 ($5/m) el total sube otros $20.
- **R5** — `parametros.premarcoCodigo` guarda el código elegido.
- **R6** — `premarcoCodigo = NO-EXISTE` devuelve error en `premarcoCodigo`.

## Evidencia

- `tests/Feature/PremarcoTapajuntasTest.php` — 5 tests con los importes exactos.
- En producción: tabaquera 700×500 con premarco 4570 y tapajuntas ADR 62 → U$S 229 (antes 191 sin
  ellos), creada y borrada.
