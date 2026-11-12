# Spec 0001 — Plataforma y datos

**Estado:** implementada
**Depende de:** —

## Problema

Alucal v5.5.8 es una aplicación Java de escritorio (Swing + SQLite) que no puede publicarse en un
hosting compartido ni usarse desde varios puestos. Necesitamos la misma información (obras,
aberturas, catálogos, precios) en una aplicación web PHP.

## Alcance

- Esquema de base de datos equivalente al de las 6 bases SQLite originales.
- Importación de catálogos y datos históricos.
- Autenticación básica y navegación (obras, clientes).

Fuera de alcance: edición de catálogos, multiusuario avanzado, importación incremental.

## Historias de usuario

- **HU1** — Como carpintería quiero ver mis obras y clientes en el navegador para consultarlos
  desde cualquier equipo.
- **HU2** — Como responsable de datos quiero que los catálogos del Java (perfiles, accesorios,
  vidrios, series, tipologías, definiciones de parámetros) estén completos, para no recargarlos a
  mano.

## Requisitos

| ID | Requisito |
|---|---|
| R1 | El esquema cubre todas las tablas de las 6 bases `.s3db` (core, definiciones, accesorios, vidrios, configuración, clientes). |
| R2 | Las tablas duplicadas entre bases se unifican en una sola (`Serie`, `Tipologia`, `TipologiaSerie`, `Cliente`, `Usuario`). |
| R3 | Un comando importa las 6 bases conservando ids y textos con acentos. |
| R4 | Existe autenticación por usuario/contraseña con alta inicial de administrador. |
| R5 | La aplicación lista obras con búsqueda, alta de obra y alta de cliente. |
| R6 | Las fechas guardadas como epoch en milisegundos se muestran como fecha real. |

## Criterios de aceptación

- **R1/R2** — `php artisan migrate` crea 112 tablas; no hay nombres duplicados entre orígenes.
- **R3** — `php artisan alucal:import <dir>` informa 109 tablas y 10.159 filas.
- **R4** — Un visitante en `/obras` es redirigido a `/login`; con credenciales válidas ve el listado.
- **R5** — Crear una obra redirige a su detalle con el número asignado.
- **R6** — Una obra con `fechaCreacion = 1712000213715` muestra `01/04/2024`.

## Divergencias respecto del Java

- Se unificó `Cliente` (dos definiciones incompatibles) en una tabla `clientes`.
- El login usa `users` de Laravel en lugar de `Usuario`/`Usuarios`.
- Los ids se conservan en la importación para poder trazar cada fila a su origen.

## Evidencia

- `tests/Feature/EdicionObraTest.php` — formulario, actualización, número duplicado y baja en cascada.

- `tests/Feature/NavegacionBasicaTest.php`
- `php artisan migrate --force` (112 migraciones)
- `php artisan alucal:import …` → 109 tablas · 10.159 filas
