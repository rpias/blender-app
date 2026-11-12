# Tareas 0005 — Despliegue

## Servidor

- [x] Acceso SSH con clave dedicada
- [x] Subir la aplicación a `~/alucal`
- [x] `public_html` como symlink a `alucal/public`
- [x] Permisos de `public/` y `storage/`
- [x] `composer install --no-dev` en el servidor
- [x] `.env` de producción con `APP_DEBUG=false`

## Base de datos

- [x] Crear la base MySQL en hPanel
- [x] `migrate:fresh` sobre MySQL (112 tablas)
- [x] Exportar e importar los datos (9.904 filas)
- [x] Verificar catálogos y obras en producción

## Verificación

- [x] Login por HTTP y listado de obras
- [x] Detalle de obra con total (U$S 510)
- [x] PDF y XLSX servidos en producción
- [x] Sin errores nuevos en `storage/logs`

## Pendiente

- [ ] Dominio propio y `APP_URL` definitiva
- [ ] Backup diario de la base con retención
- [ ] Rotación de logs y alerta de errores
- [ ] Cambiar la contraseña inicial del administrador
