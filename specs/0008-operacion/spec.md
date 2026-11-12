# Spec 0008 — Operación

**Estado:** planificada
**Depende de:** 0005

## Problema

La aplicación ya está en producción pero sin dominio propio, sin respaldo automático, sin roles y sin
alertas. Cualquier incidente (base corrupta, error de deploy, clave filtrada) hoy se detecta tarde.

## Alcance

- Dominio definitivo y certificado.
- Backup diario de MySQL con retención.
- Roles (admin / vendedor) y permisos por sección.
- Rotación de logs y alerta de errores.
- Cambio de contraseña inicial y política mínima.

## Requisitos

| ID | Requisito |
|---|---|
| R1 | El sitio responde en el dominio propio con HTTPS y `APP_URL` correcta. |
| R2 | Un cron diario genera `mysqldump` comprimido y conserva los últimos 7 días. |
| R3 | El backup se puede restaurar y queda verificado una vez. |
| R4 | Sólo `admin` puede editar catálogos y usuarios; `vendedor` sólo obras y presupuestos. |
| R5 | Los logs rotan semanalmente y se conservan 4 semanas. |
| R6 | Un error 500 deja entrada en el log y notifica por correo o panel. |

## Criterios de aceptación

- `https://<dominio>/login` responde 200 con certificado válido.
- `ls ~/backups/*.sql.gz` muestra 7 archivos con fechas consecutivas.
- Restaurar el backup más reciente en una base de prueba devuelve los mismos conteos (7 obras, 8
  aberturas, 1.728 perfiles).
- Un `vendedor` recibe 403 en `/catalogos/coeficientes`.
- `storage/logs` no supera 4 archivos.

## Riesgos

- El backup corre en el mismo servidor: si se pierde el hosting, se pierde el respaldo. Se recomienda
  copia externa (rclone a un destino remoto) en una segunda etapa.
