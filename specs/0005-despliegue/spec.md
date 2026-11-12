# Spec 0005 — Despliegue

**Estado:** implementada
**Depende de:** 0001–0004

## Problema

El sitio tiene que correr en un hosting compartido (Hostinger) sin acceso root, sin Node y sin
servicios externos: sólo PHP, MySQL y SSH.

## Alcance

- Estructura de directorios segura (aplicación fuera del web root).
- Publicación reproducible por SSH + rsync.
- Base de datos en MySQL con datos importados.
- Configuración de producción (cachés, `APP_DEBUG=false`).

## Requisitos

| ID | Requisito |
|---|---|
| R1 | La aplicación vive en `~/alucal` y el dominio apunta a `~/alucal/public`. |
| R2 | Los archivos estáticos son legibles por el usuario del servidor web. |
| R3 | Un script publica cambios y deja la app operativa en un solo comando. |
| R4 | La base es MySQL con las 112 tablas y los datos migrados. |
| R5 | No se versionan secretos: `.env` y credenciales quedan fuera del repositorio. |

## Criterios de aceptación

- **R1** — `public_html` es un symlink a `~/alucal/public`.
- **R2** — `GET /css/alucal.css` → `200 text/css` (con permisos `644` en `public/`).
- **R3** — `deploy/publicar.sh` sincroniza, migra y cachea sin intervención manual.
- **R4** — `select count(*) from perfil` en producción → 1728.
- **R5** — `.env`, `database.sqlite` y los dumps de datos están en `.gitignore`.

## Evidencia

- Sitio en producción: `https://springgreen-mole-850582.hostingersite.com`
- `docs/despliegue.md`
