# Spec 0016 — Colocación

**Estado:** implementada
**Depende de:** 0002, 0004

## Problema

La tabla `precio_colocacion` estaba importada con **12 tipos de colocación** (atornillar, amurar, sacar
y colocar, mamparas, dentro de premarco…) con precio base y precio por metro lineal, y **nunca se
usaba**: el presupuesto no cobraba la colocación. Es un costo real que se cobraba de memoria o se
perdía.

## Alcance

- Elegir el tipo de colocación en el alta de una abertura, o dejarlo vacío.
- Cobro por abertura: precio base + perímetro en metros × precio por metro.
- La colocación aparece como **línea propia** del presupuesto, separada del precio de la abertura.

Fuera de alcance: colocación de vanos compuestos como bloque y horas de taller.

## Requisitos

| ID | Requisito |
|---|---|
| R1 | El formulario permite elegir un tipo de colocación o “sin colocar”. |
| R2 | El importe es `base + 2×(ancho+alto)/1000 × precioPorMetro`, redondeado. |
| R3 | La colocación se muestra como línea aparte en PDF, Excel y CSV, con su subtotal. |
| R4 | El total de la obra la incluye. |
| R5 | Un tipo inexistente se rechaza con error de validación. |
| R6 | Sin tipo elegido, nada cambia respecto de hoy. |

## Criterios de aceptación

- **R2** — 1.000 × 1.000 con `ABERTURA_ATORNILLAR` (base 20, 12/m): 4 m → **$68**.
- **R3/R4** — Abertura de $200 + colocación de $68: el presupuesto muestra dos líneas y total **$268**.
- **R6** — Sin colocación el presupuesto tiene una sola línea y total $200.

## Evidencia

- `tests/Feature/ColocacionTest.php` — 6 tests (fórmula, sin tipo, línea del presupuesto, total,
  detalle de obra y validación).
- En producción: tabaquera 700×500 con colocación → abertura $191 y obra $750 (510 + 191 + 49 de
  colocación); al borrarla volvió a $510.
