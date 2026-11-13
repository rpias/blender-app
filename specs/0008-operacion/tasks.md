# Tareas 0008 — Operación

## Dominio y HTTPS

- [ ] Apuntar el dominio al hosting (requiere DNS del cliente)
- [ ] Actualizar `APP_URL` y verificar el certificado

## Backup

- [x] Script `deploy/backup.sh` (mysqldump comprimido, retención 7, log)
- [x] Primera copia generada y validada (`alucal-20261112-1416.sql.gz`, 119 tablas)
- [ ] Programar el cron en hPanel (el usuario SSH no tiene `crontab`):
      `15 3 * * * /home/u746296444/alucal/deploy/backup.sh >/dev/null 2>&1`
- [x] Restaurar una copia y validar los datos en una base temporal (MariaDB 10.4 local):
      7 obras, 8 aberturas y 1.728 perfiles, idénticos a producción
- [x] Backup portable: el script quita la marca de sandbox de MariaDB 11 que rompe clientes 10.x

## Roles

- [x] `User::esAdministrador()` sobre `users.rol`
- [x] Middleware `admin` (`SoloAdministrador`) aplicado a catálogos
- [x] Enlace "Catálogos" oculto para vendedores
- [x] Tests: vendedor recibe 403 en GET y POST, y no ve el enlace
- [x] Gestión de usuarios desde la UI (alta, cambio de rol, baja, sin autoborrado)
- [ ] Aplicar el rol a la edición de obras (hoy cualquier usuario autenticado edita)

## Logs y monitoreo

- [x] Canal `daily` con retención de 14 días en producción
- [ ] Alerta ante errores 500 (revisar `laravel-*.log` o servicio externo)
- [ ] Rotación verificada con el paso de los días

## Pendiente por dependencia externa

Los ítems sin marcar necesitan acciones en hPanel o DNS que no se pueden hacer por SSH.
