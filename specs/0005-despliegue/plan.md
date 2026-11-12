# Plan 0005 — Despliegue

## Topología en el servidor

```
/home/u746296444/
├── alucal/                     aplicación Laravel completa (fuera del web root)
│   ├── public/                 único directorio expuesto
│   ├── storage/                logs, sesiones, caché (escritura del usuario PHP)
│   └── .env                    configuración de producción (no versionado)
└── domains/<sitio>/
    └── public_html -> /home/u746296444/alucal/public
```

## Requisitos del hosting

| Recurso | Valor observado |
|---|---|
| PHP | 8.3.36 con `pdo_mysql`, `zip`, `gd`, `intl`, `mbstring`, `sqlite3` |
| Composer | 2.8.8 |
| Base | MariaDB 11.8.9 |
| Límites | `memory_limit` 1536 MB, sin `max_execution_time` |

## Publicación

`alucal-web/deploy/publicar.sh`:

1. Verifica la conexión SSH.
2. `composer install --no-dev --optimize-autoloader` en local.
3. `rsync` de la app (excluye `vendor`, `tests`, `.env`, base local y logs).
4. Ajusta permisos de `public/` y `storage/`.
5. En el servidor: `migrate --force`, `config:cache`, `route:cache`, `view:cache`.

Variables: `SSH_HOST`, `SSH_PORT`, `SSH_USER`, `SSH_KEY`, `APP_DIR`, `WEB_DIR`.

## Base de datos

`php artisan alucal:exportar-mysql storage/app/alucal-datos.sql` genera los `INSERT` compatibles con
MySQL (112 tablas, ~9.900 filas). En el servidor se corre `migrate:fresh` y luego se importa el
archivo con el cliente `mysql`.

## Incidentes resueltos

| Síntoma | Causa | Solución |
|---|---|---|
| `403` en `/css/alucal.css` | rsync preservó permisos `600` | `chmod 644` en `public/` (incluido en el script) |
| Migración falla en MySQL | nombre de índice de 65 caracteres | índice con nombre explícito `uq_001` |
| `Permission denied (publickey)` | clave local con passphrase | clave dedicada sin passphrase para deploy |
| `public_html` no cambiaba | symlink creado dentro del directorio | borrar el directorio y crear el symlink |

## Pendiente

- [ ] Dominio definitivo y certificado
- [ ] Backup automático de MySQL con retención
- [ ] Monitoreo de errores (log rotado y alerta)
