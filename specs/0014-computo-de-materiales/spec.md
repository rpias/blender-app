# Spec 0014 — Cómputo de materiales

**Estado:** implementada
**Depende de:** 0002, 0003, 0009

## Problema

El cálculo de cada abertura ya guarda sus barras, accesorios y vidrios, pero el taller no puede ver
esa información junta: para cortar necesita la **lista de corte** (qué perfil, qué medida y cuántos
tramos) y los metros/kilos totales. Hoy eso se reconstruye a mano.

## Alcance

- Cómputo por obra: perfiles agrupados por código y medida de corte, accesorios por código y vidrios
  por medida.
- Pantalla consultable y exportación a Excel.
- Incluye los hijos de los vanos compuestos.

Fuera de alcance: optimización de cortes sobre barras de 6 m, stock y descuento de materiales.

## Requisitos

| ID | Requisito |
|---|---|
| R1 | El cómputo agrupa los perfiles por código y largo, sumando cantidad, metros y kilos. |
| R2 | Los accesorios se agrupan por código sumando cantidades. |
| R3 | Los vidrios se agrupan por tipo y medida, con cantidad y m². |
| R4 | Los vanos compuestos aportan los materiales de sus hijos. |
| R5 | Una obra sin aberturas calculadas muestra un mensaje y no falla. |
| R6 | Se puede exportar el cómputo a Excel. |

## Criterios de aceptación

- **R1** — Dos aberturas con 2 tramos de 1.000 mm dan una fila `9500 · 1.000 mm · 4 tramos · 4,00 m`.
- **R4** — Un vano con dos hijos suma los materiales de ambos.
- **R5** — Obra vacía: la pantalla responde 200 con “La obra no tiene perfiles calculados”.
- **R6** — `GET /obras/{obra}/computo.xlsx` devuelve un archivo spreadsheet.

## Evidencia

- `tests/Feature/ComputoMaterialesTest.php` — 5 tests (agrupado de perfiles, accesorios y vidrios,
  pantalla, Excel y obra vacía).
- Captura de la pantalla en obra 7: 6 cortes distintos, 29,30 m y 19,96 kg.
