# Spec 0006 — Catálogos editables

**Estado:** implementada
**Depende de:** 0001, 0002

## Problema

Hoy los precios y coeficientes sólo se pueden cambiar por SQL o reimportando el Java. La carpintería
necesita ajustar márgenes, precios por kilo, precios de vidrio y de accesorios desde la aplicación,
sin depender de un técnico.

## Alcance

- Edición de coeficientes de cálculo.
- Edición de precios por kilo (terminación × RPT).
- Edición de márgenes y desperdicios por tipología × serie.
- Edición de precio de accesorios.
- Edición de peso y descripción de perfiles.
- Edición de precio, mínimo y margen de vidrios.

Fuera de alcance: alta/baja de perfiles y accesorios, importación desde Excel, historial de cambios.

## Historias de usuario

- **HU1** — Como dueño quiero subir el precio del kilo de una terminación y que los presupuestos
  nuevos salgan con el valor actualizado.
- **HU2** — Como administración quiero corregir el precio de un accesorio puntual sin tocar el resto.
- **HU3** — Como responsable quiero ver qué coeficientes están en uso para entender un presupuesto.

## Requisitos

| ID | Requisito |
|---|---|
| R1 | Existe una sección "Catálogos" accesible desde la navegación, sólo para usuarios autenticados. |
| R2 | Cada catálogo muestra sus filas con los campos editables en un formulario. |
| R3 | Guardar un catálogo valida cada valor (numérico, rango, requerido) y no deja el catálogo a medias. |
| R4 | Los cambios impactan de inmediato en el cálculo de precios nuevos. |
| R5 | Los catálogos grandes (perfiles, accesorios) tienen buscador y paginación. |
| R6 | Un valor inválido devuelve el formulario con el error y sin modificar la base. |

## Criterios de aceptación

- **R2** — `GET /catalogos/coeficientes` muestra las 29 claves con su valor.
- **R3** — Enviar `valor = "abc"` en un coeficiente devuelve error de validación y el valor en la
  base no cambia.
- **R4** — Cambiar `MARGEN_DVH_CARPINTERIA` a `1,5` y recalcular una abertura DVH aumenta el precio.
- **R5** — `GET /catalogos/perfiles?buscar=ADR 1470` devuelve sólo los perfiles que coinciden.
- **R6** — Un `precio` negativo en accesorios se rechaza con mensaje.

## Divergencias

- El Java editaba catálogos con pantallas Swing; acá es una tabla HTML con guardado por catálogo.
- No se permite crear ni borrar filas: el catálogo sigue siendo el del sistema original.
