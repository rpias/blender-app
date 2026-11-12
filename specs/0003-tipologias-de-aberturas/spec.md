# Spec 0003 — Tipologías de aberturas

**Estado:** parcial (5 de 20 tipologías)
**Depende de:** 0001, 0002

## Problema

En el Java cada tipología y serie tiene su propio método con descuentos de corte hardcodeados
(`AberturaServiceCorrediza` tiene 15.542 líneas). Necesitamos calcular las cantidades físicas
(perfiles, accesorios, vidrios) de las tipologías que la carpintería realmente usa, sin arrastrar
esas constantes al código PHP.

## Alcance

Tipologías implementadas, todas a 2 hojas:

| Tipología | Serie | Calculadora |
|---|---|---|
| Marco fijo | HA 62, Serie 20, Rotonda 640 | `CalculadoraMarcoFijo` |
| Corrediza Domina | Domina | `CalculadoraCorredizaDomina` |
| Corrediza HA 62 | HA 62 | `CalculadoraCorredizaHA62` |
| Corrediza Serie 20 | Serie 20 | `CalculadoraCorredizaS20` |
| Corrediza Serie 25 | Serie 25 | `CalculadoraCorredizaS25` |

## Historias de usuario

- **HU1** — Como vendedor quiero cargar una abertura con sus parámetros y ver el precio al instante.
- **HU2** — Como responsable quiero recalcular las aberturas históricas con el motor nuevo, para que
  las obras viejas muestren importes.

## Requisitos

| ID | Requisito |
|---|---|
| R1 | Cada calculadora devuelve barras (perfil, peso kg/m, cantidad, largo mm, parte), accesorios (código, precio unitario, cantidad) y vidrios (ancho/alto en metros, costo, mínimo, margen). |
| R2 | Las constantes de corte por tipología-serie viven en `constantes_calculo`; si faltan, el cálculo falla con mensaje claro y no con un error genérico. |
| R3 | El ancho de perfil que el catálogo no trae (contravidrio, divisor) se resuelve en `perfil_medidas`. |
| R4 | Los parámetros guardados por el Java (EAV `valor_parametro_particular*`) se leen y se traducen a los parámetros del calculador. |
| R5 | Al calcular se guarda un snapshot (`parametros`, `lineas`, `precioUnitario`, `calculadoEn`) en la abertura. |
| R6 | Un comando recalcula las aberturas importadas e informa las que no puede calcular y por qué. |
| R7 | Las aberturas sin elemento de obra (huérfanas del Java) se pueden archivar y borrar con respaldo. |

## Criterios de aceptación

- **R1** — Marco fijo 1000×1000 HA 62: 2 barras de marco de 1000 mm y 4 contravidrios de 956 mm.
- **R2** — Serie sin constantes: `SerieSinConstantes` con el nombre de la constante faltante.
- **R4** — La abertura 19 del histórico se mapea a Domina con enganche SIMPLE y rueda 193155.
- **R5** — `precioUnitario` queda persistido y el detalle muestra el importe sin recalcular.
- **R6** — `alucal:recalcular` informa "8 recalculadas · 0 omitidas" con los datos actuales.
- **R7** — `alucal:limpiar-huerfanas --force` archiva 17 aberturas y las elimina.

## Divergencias

- **Snapshot en lugar de recálculo**: el Java recalcula al abrir la obra
  (`DaoAberturaSimple.java:223`); acá se persiste el resultado para que el presupuesto sea
  reproducible y auditable.
- **Constantes como datos**: se movieron a `constantes_calculo` con la cita del método Java de
  origen.
- **Fallback determinista**: donde el Java iteraba un `HashMap` (orden no especificado) se ordena por
  `idSerie`.

## Evidencia

- `tests/Feature/CalculoAberturaTest.php`, `CalculoCorredizaTest.php`, `CalculoDominaTest.php`,
  `CalculoHA62Test.php`, `RecalculoAberturasTest.php`
- `docs/reverse-engineering/aberturas.md`
