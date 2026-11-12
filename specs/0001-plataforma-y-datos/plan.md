# Plan 0001 — Plataforma y datos

## Stack

- Laravel 13 sobre PHP 8.3.
- MySQL 8 / MariaDB en producción; SQLite en desarrollo y tests.
- Sin Node ni build de frontend: CSS propio en `public/css/alucal.css`.

## Esquema

- 111 tablas de dominio generadas desde los `CREATE TABLE` de SQLite con
  [`tools/gen_schema.py`](../../tools/gen_schema.py), más `users` de Laravel y la extensión de
  perfiles.
- Los nombres se conservan en español y en snake_case (`abertura_simple`, `cant_barras`, …) para que
  la importación sea directa y trazable.
- Las columnas mantienen el camelCase original (`idTipologia`, `fechaCreacion`): se priorizó la
  trazabilidad con el sistema viejo sobre la convención de Laravel.

## Importación

`App\Console\Commands\ImportarAlucal`:

- Orden de precedencia por archivo (core → definiciones → catálogo → vidrios → configuración →
  clientes): la primera base que define una tabla gana.
- Renombres explícitos para tablas unificadas (`Cliente` → `clientes`, `Usuario` → `users`).
- Normaliza textos en Windows-1252 a UTF-8 (los catálogos vienen con acentos mal codificados).
- Inserta por lotes de 200 filas.

## Fechas

El Java guarda epoch en milisegundos dentro de columnas `DATE`. `Obra::comoFecha()` acepta ms,
segundos o ISO y se aplica con accesores en `fechaCreacion`, `fechaUltModificacion` y
`proximaActionFecha`.

## Riesgos

- Dependencia del esquema original: si el cliente actualiza Alucal, hay que regenerar e importar.
- Los catálogos tienen duplicados de código de accesorio; se toma el primero por código.
