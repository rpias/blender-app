# Despliegue

## Producción actual

| | |
|---|---|
| URL | `https://springgreen-mole-850582.hostingersite.com` |
| Host SSH | `185.173.111.90` · puerto `65002` · usuario `u746296444` |
| App | `/home/u746296444/alucal` |
| Web root | `~/domains/springgreen-mole-850582.hostingersite.com/public_html` → symlink a `alucal/public` |
| Base | `u746296444_alucal` (MariaDB 11.8) |
| PHP | 8.3.36 |

Las credenciales (contraseña MySQL, `.env`, clave SSH) **no** están en el repositorio.

## Publicar cambios

```bash
cd alucal-web
./deploy/publicar.sh
```

El script usa por defecto el host, usuario, clave (`~/.ssh/alucal_hostinger`) y rutas de producción;
todo se puede sobreescribir por variables de entorno.

Qué hace:

1. Verifica SSH y muestra la versión de PHP del servidor.
2. `composer install --no-dev --optimize-autoloader` en local.
3. `rsync` de la app (excluye `vendor`, `tests`, `.env`, base local, logs y cachés).
4. Ajusta permisos: `public/` legible por el usuario web, `storage/` escribible.
5. En el servidor: `migrate --force`, `config:cache`, `route:cache`, `view:cache`.

## Base de datos

Para llevar datos locales a producción:

```bash
# local
php artisan alucal:exportar-mysql storage/app/alucal-datos.sql

# subir y cargar
rsync -az storage/app/alucal-datos.sql u746296444@185.173.111.90:alucal/
ssh … 'cd ~/alucal && mysql -h 127.0.0.1 -u USUARIO -p CLAVE BASE < alucal-datos.sql'
```

El dump excluye `migrations`, `cache`, `sessions` y `jobs`, y desactiva las claves foráneas durante
la carga.

## Primer despliegue (resumen)

1. `rsync` de la app a `~/alucal`.
2. `composer install --no-dev` en el servidor.
3. `.env` de producción + `php artisan key:generate`.
4. Crear la base en hPanel y correr `php artisan migrate --force`.
5. Importar el dump.
6. Apuntar `public_html` al `public/` de la app.
7. `chmod` de `public/` (644 archivos, 755 directorios).
8. `config:cache`, `route:cache`, `view:cache`.

## Problemas conocidos y solución

| Síntoma | Causa | Solución |
|---|---|---|
| CSS/JS dan 403 | permisos `600` por rsync | `chmod 644` en `public/` |
| `Permission denied (publickey)` | clave con passphrase | usar `~/.ssh/alucal_hostinger` (sin passphrase) |
| Migración falla en MySQL | índice de 65 caracteres | nombre explícito corto en la migración |
| `public_html` no apunta a la app | `ln -s` dentro del directorio existente | borrar el directorio y recrear el symlink |
| Fechas como `26/06/58597` | epoch en ms en columna `DATE` | accesores `Obra::comoFecha()` |

## Backups

`deploy/backup.sh` corre `mysqldump` comprimido con retención de 7 días y quita la marca
`/*M!999999` que MariaDB 11 agrega al inicio: sin eso, un cliente 10.x no puede restaurar el archivo.

Restauración verificada el 2026-11-13: copia importada en una base limpia devolvió 7 obras,
8 aberturas y 1.728 perfiles, igual que producción.

Cron pendiente de cargar en hPanel (el usuario SSH no tiene binario `crontab`):

```
15 3 * * * /home/u746296444/alucal/deploy/backup.sh >/dev/null 2>&1
```

## Checklist antes de dar por publicado

- [ ] `APP_DEBUG=false` y `APP_ENV=production`
- [ ] Contraseña del administrador cambiada
- [ ] `GET /login` responde 200 y con estilos
- [ ] Un PDF de presupuesto se descarga
- [ ] `storage/logs/laravel.log` sin errores nuevos
