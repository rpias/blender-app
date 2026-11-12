# Spec 0007 — Más tipologías

**Estado:** parcial (batiente y corrediza 2+1 Doppia)
**Depende de:** 0003

## Problema

Hoy se pueden presupuestar 5 variantes (marco fijo y cuatro corredizas de 2 hojas). Las obras reales
incluyen batientes, proyectantes, guillotinas, tabaqueras, paños fijos, 2+1, 2+2 y aberturas
compuestas. Sin ellas la aplicación no reemplaza al Java para el día a día.

## Alcance

Incorporar por orden de uso estimado:

1. ~~**Batiente** 1 y 2 hojas (`AberturaServiceBatiente`).~~ **implementado**
2. ~~**Corrediza 2+1** Doppia~~ **implementado** · 2+2 pendiente.
3. **Paño fijo** en corrediza (`AberturaServiceCorredizaPañoFijo`).
4. **Proyectante** y **Guillotina**.
5. **Tabaquera** y **Oscilobatiente**.
6. **Aberturas compuestas** (`AberturaCompuesta_2` + conectores + vano dividido).

Fuera de alcance: fachadas vidriadas, barandas, estructuras de aluminio y uniblock mix.

## Requisitos

| ID | Requisito |
|---|---|
| R1 | Cada tipología nueva se implementa como una calculadora propia con sus constantes en `constantes_calculo`. |
| R2 | El formulario de la obra ofrece la tipología con sus parámetros reales (no campos libres). |
| R3 | El cálculo incluye los accesorios propios de la tipología (bisagras, felpilla, cierres). |
| R4 | Cada calculadora tiene al menos un test con medidas de referencia y un caso de error. |
| R5 | Las aberturas históricas de esa tipología se recalculan con `alucal:recalcular`. |

## Criterios de aceptación

- Batiente 1 hoja 800×2000 → hoja `ancho − 39` / `alto − 39`, vidrio `(hoja − 92)`.
- 2+1 → el paño fijo usa `getAcristalamiento2()` y el vidrio se emite como paño independiente.
- Recalcular el histórico deja **0 omitidas** para las tipologías soportadas.
- Una serie sin constantes devuelve `SerieSinConstantes` con la clave faltante.

## Evidencia

- `tests/Feature/CalculoBatienteTest.php` — 1 hoja recta (hoja 761 × 1961, vidrio 669 × 1869 mm),
  2 hojas curvas con DVH (6234 + 6223 + 6231), cantidad 3 y error de hoja.
- `app/Services/Aberturas/CalculadoraBatiente.php`
- `tests/Feature/CalculoDoppiaTest.php` — 2+1 con umbral bajo (2473 a 1430 mm) y alto (1340 mm),
  refuerzo 1152 y dos vidrios.

## Riesgo principal

Cada serie agrega decenas de líneas de constantes; el trabajo es de transcripción y verificación, no
de diseño. Se avanza tipología por tipología, cerrando con test y recálculo del histórico.
