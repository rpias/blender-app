# Tareas 0006 — Catálogos editables

## Infraestructura

- [x] Descriptor `RecursoCatalogo` (tabla, columnas, reglas, claves, orden, búsqueda)
- [x] Registro `Recursos` con los 6 catálogos
- [x] `CatalogoController` único (index + update con guardado transaccional)
- [x] Vista `catalogos/index.blade.php` con tabla editable, buscador y paginación
- [x] Pestañas entre catálogos y enlace en la navegación
- [x] Validación por campo antes de escribir
- [x] Soporte de claves compuestas (`idTipologia` + `idSerie`)

## Catálogos

- [x] Coeficientes de cálculo (29 claves)
- [x] Precio por kilo (terminación × RPT)
- [x] Márgenes y desperdicios por tipología × serie
- [x] Accesorios (precio y `cantPrecio`)
- [x] Perfiles (descripción y peso)
- [x] Vidrios (precio, mínimo y margen)

## Verificación

- [x] 9 tests de catálogos (permisos, guardado, validación, clave compuesta, búsqueda, 404)
- [x] Confirmar que el motor de precios lee el valor actualizado
- [x] Publicado en producción y verificado por HTTP

## Alta y baja

- [x] Alta de accesorios y perfiles con valores por defecto del esquema
- [x] Baja por fila con confirmación
- [x] Sólo los catálogos que lo declaran permiten alta/baja (coeficientes no)
- [x] Verificado en producción (TEST-999 creado y eliminado)

## Pendiente

- [ ] Historial de cambios y quién editó
- [ ] Restringir la sección al rol administrador (spec 0008)
