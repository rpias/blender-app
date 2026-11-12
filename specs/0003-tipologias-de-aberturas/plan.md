# Plan 0003 — Tipologías de aberturas

## Estructura

```
app/Services/Aberturas/
  Constantes.php                 constantes_calculo + perfil_medidas
  LineasAbertura.php             DTO de salida (barras, accesorios, vidrios)
  CalculadoraMarcoFijo.php
  CalculadoraCorredizaDomina.php
  CalculadoraCorredizaHA62.php
  CalculadoraCorredizaS20.php
  CalculadoraCorredizaS25.php
  CalcularAberturaSimple.php     orquesta: elige calculadora, calcula precio, guarda snapshot
  LectorParametros.php           lee el EAV del Java
  MapeadorParametros.php         traduce EAV → parámetros del calculador
  ParametrosFormulario.php       opciones para los formularios
```

## Selección de calculadora

`CalcularAberturaSimple::lineas()` despacha por `idTipologia` y `idSerie`:

| Tipología | Serie | Calculadora |
|---|---|---|
| 6 | cualquiera con constantes | Marco fijo |
| 3 | 2 | Domina |
| 3 | 10 | HA 62 |
| 3 | 19 | Serie 20 |
| 3 | 20 | Serie 25 |

Si la combinación no está soportada se lanza `SerieSinConstantes` con un mensaje accionable.

## Constantes

`constantes_calculo(idTipologia, idSerie, clave, valor)` con claves como
`marcoAnchoDescuentoMM`, `contravidrioAltoDescuentoMM`, `divisorGapMM` y la marca `soporte` que
habilita la serie en los formularios. Se cargan con `ConstantesCalculoSeeder`, citando el método Java
de origen.

## Lectura del histórico

El Java guarda cada abertura como filas EAV: `valor_parametro_particular` + una tabla tipada hermana
con el mismo `id`. `LectorParametros` resuelve esa relación y `MapeadorParametros` traduce a los
parámetros del calculador, incluyendo el vidrio (`VIDRIOSINCAMARA@1`, `DVH@1@12@2`).

## Riesgos

- Cada serie nueva implica leer su método Java y cargar sus constantes; no hay fórmula genérica.
- Los cálculos omiten accesorios opcionales (mosquitero, uniblock, umbral) mientras no se porten:
  el precio queda por debajo del original en esas variantes.
