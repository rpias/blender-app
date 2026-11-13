# Plan 0009 — Aberturas compuestas

## Modelo

```
elemento_obra (tipo ABERTURA_COMPUESTA)
   └── vano_division (orientacion, posicionMM, codigoConector, terminacion)
   └── elemento_obra hijo (tipo ABERTURA_SIMPLE)  ← ya existe, se le agrega idVano
```

Se reutiliza `elemento_obra` para el contenedor y una tabla nueva para las divisiones, en lugar de
revivir `abertura_compuesta_2` (que en el cliente está vacía y sin divisiones).

### Migración

```
vano_division
  id, idElementoVano, orientacion (VERTICAL|HORIZONTAL), posicionMM,
  codigoConector, terminacion, timestamps

elemento_obra.idElementoVano  (nullable, FK a elemento_obra)
```

## Cálculo

`App\Services\Aberturas\CalcularVano`:

1. Suma `precioUnitario × cantidad` de los hijos.
2. Por cada división: metros × kg/m del conector × U\$S/kg de la terminación × coeficiente de
   desperdicio × margen de marco, redondeado por bloque.
3. Multiplica por la cantidad del vano.
4. Guarda snapshot en `elemento_obra.datos` (json) con hijos, divisiones y total.

## Rutas

| Método | Ruta | Acción |
|---|---|---|
| POST | `/obras/{obra}/vanos` | Crear vano con hijos y divisiones |
| PATCH | `/vanos/{elemento}` | Recalcular / cambiar conector |
| DELETE | `/vanos/{elemento}` | Deshacer el vano (conserva hijos) |

## UI

- En el detalle de obra, botón **Agrupar en vano** que abre un formulario con: aberturas de la obra
  sin vano, perfil conector y cantidad de divisiones.
- La tabla de la obra muestra el vano como fila padre y los hijos indentados.

## Reportes

`elementosPresupuestables()` pasa a devolver vanos (con hijos) y aberturas sueltas: el PDF ya recorre
esa colección, así que se agrega una fila por vano con el detalle de hijos en una sub-lista.

## Riesgos

- El precio del conector depende de `precios_por_kilo` y del margen de marco de la tipología del vano:
  si falta, se usa el margen de la primera abertura hija.
- Recalcular un hijo debe recalcular el vano: se resuelve en el servicio, no en el controlador.
