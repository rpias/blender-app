# Tareas 0001 — Plataforma y datos

## Esquema

- [x] Extraer los `CREATE TABLE` de las 6 bases `.s3db`
- [x] Generar migraciones Laravel (`tools/gen_schema.py`)
- [x] Unificar tablas duplicadas (`clientes`, `serie`, `tipologia`, `tipologia_serie`)
- [x] Extender `users` con nombre, rol y activo
- [x] Correr las 112 migraciones en SQLite y en MySQL

## Datos

- [x] Comando `alucal:import` con precedencia por archivo
- [x] Normalización de textos Windows-1252 → UTF-8
- [x] Importar 10.159 filas (109 tablas)
- [x] Verificar catálogos: 1.728 perfiles, 1.267 accesorios, 48 series, 20 tipologías

## Aplicación

- [x] Login/logout con sesión y `activo`
- [x] Listado de obras con búsqueda y paginación
- [x] Alta de obra y de cliente
- [x] Detalle de obra con elementos y totales
- [x] Accesores de fecha para epoch en milisegundos
- [x] Layout y CSS propio (sin build)

## Pendiente

- [ ] Edición y baja de obras/clientes
- [ ] Multiusuario con roles diferenciados (admin/vendedor)
- [ ] Importación incremental cuando el cliente actualice Alucal
