# Plan 0006 — Catálogos editables

## Enfoque

Un controlador genérico y una vista única, con un registro de recursos declarativo. Evita repetir
controlador, vista y validación por cada catálogo.

```
app/Servicios/Catalogos/RecursoCatalogo.php    descriptor (título, modelo, columnas, reglas)
app/Servicios/Catalogos/Recursos.php           los 6 catálogos
app/Http/Controllers/CatalogoController.php    index + update (guardado masivo)
resources/views/catalogos/index.blade.php      tabla editable + buscador
```

## Recursos

| Clave | Tabla | Campos editables | Buscador |
|---|---|---|---|
| `coeficientes` | `coeficientes_de_calculo_de_precio` | `valor` | — |
| `precios-kilo` | `precios_por_kilo` | `valor` | terminación |
| `margenes` | `margenes_desperdicios_coef_ajuste` | `MargenMH`, `DespMH`, `MargenMosq`, `DespMosq` | — |
| `accesorios` | `accesorio` | `precio`, `cantPrecio`, `activo` | código, descripción |
| `perfiles` | `perfil` | `Peso`, `Descripcion` | número, descripción |
| `vidrios` | `vidrio_sin_camara` | `precio`, `precioMinimo`, `margen` | — |

## Guardado

- El formulario envía `filas[<id>][campo] = valor`.
- Se valida el conjunto completo antes de escribir (`Validator::make` sobre el array).
- Se actualiza en una transacción: o se guardan todas las filas o ninguna.
- Tras guardar se limpia la caché de configuración de coeficientes (`config:clear` no hace falta:
  `Coeficientes` cachea por request).

## Validaciones

| Campo | Regla |
|---|---|
| `valor` de coeficiente | `required|numeric` |
| precio de accesorio | `required|numeric|min:0` |
| `cantPrecio` | `required|integer|min:1` |
| peso de perfil | `required|numeric|min:0` |
| margen | `required|numeric|min:1` |

## Riesgos

- Editar un coeficiente afecta todos los presupuestos nuevos; los ya generados quedan con su
  snapshot de precio.
- No hay historial: si se cambia un valor por error, se recupera desde un backup de la base.
