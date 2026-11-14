# Spec 0013 — Formato de moneda

**Estado:** implementada
**Depende de:** 0002, 0004, 0010

## Problema

Los importes se mostraban sin decimales y con criterios distintos según la pantalla: la obra y el
panel usaban `0` decimales, el CSV usaba punto decimal y el catálogo de accesorios mostraba el precio
**en centavos** (2550 en lugar de 25,50), lo que confunde al editar.

## Alcance

- Un único formato de importes en toda la aplicación: dos decimales, miles con punto y decimal con
  coma (`1.882,00`).
- El catálogo de accesorios edita en pesos y guarda en centavos.
- Excel con formato de número de dos decimales; CSV con dos decimales.

Fuera de alcance: cambiar la precisión del cálculo (el motor sigue redondeando a peso entero, igual
que el Java original).

## Requisitos

| ID | Requisito |
|---|---|
| R1 | Todo importe mostrado en pantalla usa dos decimales (`U$S 1.882,00`). |
| R2 | El PDF del presupuesto usa el mismo formato. |
| R3 | El CSV exporta importes con dos decimales y el Excel los formatea como número. |
| R4 | El catálogo de accesorios muestra y edita el precio en pesos con dos decimales, guardando centavos. |
| R5 | Las cantidades y las medidas en milímetros siguen sin decimales. |

## Criterios de aceptación

- **R1** — El detalle de obra muestra `255,00` en unitario y `U$S 510,00` en el total.
- **R3** — El CSV contiene `98,00` en unitario y subtotal.
- **R4** — Editar 25,5 en el catálogo guarda 2550 y al volver a abrir muestra `25.50` en el campo.
- **R5** — Las medidas siguen mostrándose `1.000 × 1.000`.

## Evidencia

- `tests/Feature/FormatoMonedaTest.php` — formato, redondeo, detalle de obra, panel y PDF.
- `tests/Feature/CatalogosTest.php` — precio editado en pesos, guardado en centavos y mostrado con dos
  decimales; precio por kilo con dos decimales.
- Captura del detalle de obra con `U$S 510,00`, `255,00` y `255,00`.
