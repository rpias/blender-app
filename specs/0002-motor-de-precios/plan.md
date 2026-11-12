# Plan 0002 — Motor de precios

## Componentes

```
app/Services/Precios/
  Coeficientes.php              carga y cachea coeficientes y tablas de precio
  AberturaParaPrecio.php        DTO de entrada (medidas, tipología, barras, accesorios, vidrio)
  ResultadoPrecio.php           precio, desglose e índices
  CalculadoraPrecioAbertura.php las fórmulas
```

## Fuentes de datos

| Dato | Tabla |
|---|---|
| Coeficientes globales | `coeficientes_de_calculo_de_precio` (clave → valor) |
| Margen y desperdicio por tipología-serie | `margenes_desperdicios_coef_ajuste` |
| Precio del kilo por terminación y RPT | `precios_por_kilo` |
| Premarco / tapajuntas | `precio_metro_lineal_premarco`, `precio_metro_lineal_tapajuntas` |
| Colocación | `precio_colocacion` |
| Vidrio | `vidrio_sin_camara` (precio, mínimo, margen) |

`Coeficientes` cachea por request y resuelve el precio por kilo con la misma cascada que el Java
(combinación exacta → RPT invertido → misma terminación → primer valor).

## Redondeo

El original redondea cada bloque por separado y después suma. Se replica con
`(int) round(...)` por bloque y un `round` final sobre la suma. Cambiar esto altera el total hasta
±2 unidades, por eso hay un test específico.

## DVH

El Java guarda el vidrio como `DVH@exterior@cámara@interior`. El mapeador lo parsea y el motor
resuelve el costo de cámara con `COSTO_POR_METRO_CAMARA_{6,9,12,16}`.

## Riesgos

- Los coeficientes "de fábrica" no están en el código: si una instalación tiene una fila faltante, se
  usa el default documentado (visible en el código y en los tests).
- El precio de accesorio depende de `cantPrecio`; un valor en 0 se interpreta como precio ya
  unitario.
