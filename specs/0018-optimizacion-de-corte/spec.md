# Spec 0018 — Optimización de corte

**Estado:** implementada
**Depende de:** 0014, 0017

## Problema

El cómputo dice cuántos tramos de cada medida hay que cortar, pero no **cuántas barras comprar**. Para
presupuestar el aluminio y planificar el taller hace falta saber cuántas barras de 6 m entran y cuánto
retazo queda.

## Alcance

- Reparto de los tramos por perfil en barras de largo comercial.
- Largo de barra y espesor de corte (sierra) configurables en la pantalla.
- Aviso de los tramos que no entran en una barra.

Fuera de alcance: optimización con retazos de obras anteriores, costo del aluminio y órdenes de compra.

## Requisitos

| ID | Requisito |
|---|---|
| R1 | Los tramos se agrupan por perfil. |
| R2 | Se reparten en barras de 6.000 mm por defecto, con 3 mm de sierra por corte. |
| R3 | Se informa el número de barras por perfil y el total. |
| R4 | Se informa el retazo por barra y por perfil, en mm y porcentaje. |
| R5 | Un tramo más largo que la barra se informa como “no entra”, sin romper el cálculo. |
| R6 | El largo de barra y la sierra se pueden cambiar desde la pantalla. |

## Criterios de aceptación

- **R2** — 4 tramos de 1.500 mm entran en 1 barra con retazo 0.
- **R2** — 4 tramos de 2.000 mm necesitan 2 barras (3 en la primera, 1 en la segunda) con retazo 4.000 mm.
- **R2** — Dos tramos de 3.000 mm necesitan 2 barras al descontar 3 mm por corte.
- **R5** — Un tramo de 6.500 mm aparece en “no entra”.
- **R6** — `?barra=4000&kerf=2` recalcula con esos valores.

## Evidencia

- `tests/Feature/OptimizacionCorteTest.php` — 7 tests (una barra, dos barras, mezcla de medidas,
  sierra, tramo que no entra, agrupado por perfil y pantalla).
- En producción, obra 7: **7 barras de 6.000 mm** y **12.576 mm** de retazo; con `?barra=4000` recalcula
  a barras de 4.000 mm.

## Decisión

Se usa *first-fit decreasing*: ordena de mayor a menor y coloca cada tramo en la primera barra donde
entra. Es determinista, explicable en el taller y suficiente; no busca el óptimo absoluto. El sobrante
se informa como **retazo reutilizable**, no como pérdida: lo que define la compra es el número de barras.
