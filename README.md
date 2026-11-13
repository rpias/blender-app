# Alucal — presupuestos de aberturas de aluminio

Port a Laravel de **Alucal v5.5.8**, la aplicación Java de escritorio que la carpintería usa para
presupuestar aberturas de aluminio (obras, aberturas, perfiles, accesorios, vidrios, precios y
presupuestos en PDF/Excel).

El proyecto se desarrolla con **SDD (Spec-Driven Development)**: las especificaciones en
[`specs/`](specs/) son la fuente de verdad; el código se implementa contra ellas y las tareas se
marcan ahí mismo.

```
specs/      qué se construye (requisitos, criterios de aceptación, plan, tareas)
docs/       cómo está construido y por qué (arquitectura, dominio, cálculos, despliegue)
alucal-web/ la aplicación Laravel
tools/      utilidades de migración (generadores de esquema y modelos)
```

## Estado

| | |
|---|---|
| Stack | Laravel 13 · PHP 8.3 · MySQL 8 / MariaDB |
| Código | 112 migraciones, 108 modelos Eloquent, 5 calculadoras de tipología |
| Tests | 77 tests · 277 assertions (`vendor/bin/phpunit`) |
| Producción | Hostinger compartido, `~/alucal` + `public_html` por symlink |
| Datos migrados | 7 obras, 8 aberturas, 1.728 perfiles, 1.267 accesorios |

## Qué resuelve hoy

- Obras y clientes: alta, listado, detalle con totales, edición y baja.
- Aberturas: **Marco fijo, Batiente (1 y 2 hojas), Corrediza Domina, HA 62, Serie 20, Serie 25
  Doppia 2+1 / 2+2, Corrediza + Paño Fijo Nórdical, Proyectante HA 62, Guillotina Serie 20, Tabaquera y Oscilobatiente HA 62**,
  con cálculo de perfiles, accesorios, vidrio simple y DVH.
- Precio por **margen operativo**, con coeficientes, desperdicios y precios por kilo leídos de la
  base (no hardcodeados).
- Presupuesto de obra en **PDF**, **XLSX** y **CSV**, y presupuesto de una sola abertura en PDF.
- Colores de herrajes: blanco, negro y gris (el gris sólo aplica a la guillotina, como en el original).
- **Mosquitero fijo** opcional en corredizas Serie 20 y Domina.
- **Un solo formulario de alta**: se elige el tipo de abertura y se muestran sólo sus campos.
- **Catálogos editables**: coeficientes, precio por kilo, márgenes, accesorios, perfiles y vidrios,
  con alta y baja de perfiles y accesorios.
- **Usuarios** con roles (admin/vendedor) y estado activo.
- Importación de los catálogos y datos históricos desde las bases SQLite del Java.

## Puesta en marcha local

```bash
cd alucal-web
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate
php artisan alucal:import ../Alucal_v5.5/Alucal_v5.5.8/base_de_datos
php artisan db:seed            # constantes de cálculo + usuario administrador
php artisan serve
```

Usuario inicial: `admin@alucal.local` / `alucal` (cambiar en producción).

## Comandos propios

| Comando | Para qué |
|---|---|
| `php artisan alucal:import <dir>` | Importa las 6 bases SQLite del Java |
| `php artisan alucal:recalcular` | Recalcula precios de aberturas importadas (`--dry`, `--obra=`) |
| `php artisan alucal:limpiar-huerfanas` | Archiva y borra aberturas sin elemento de obra (`--force`) |
| `php artisan alucal:exportar-mysql <archivo>` | Dump de datos para desplegar en MySQL |
| `deploy/publicar.sh` | Publica en el servidor por rsync + SSH |
| `deploy/backup.sh` | Copia diaria de MySQL con retención de 7 días (cron) |

## Documentación

- [Arquitectura](docs/arquitectura.md)
- [Dominio Alucal](docs/dominio-alucal.md)
- [Cálculos de precio y cantidades](docs/calculos.md)
- [Despliegue](docs/despliegue.md)
- [Análisis del Java original](docs/analisis-java.md)

## Origen

El binario original (`Alucal_v5.5.8/Alucal.jar`, 37 MB) y su código decompilado **no** se versionan:
son artefactos pesados y reproducibles. Los informes de ingeniería inversa sí están versionados en
[`docs/reverse-engineering/`](docs/reverse-engineering/).
