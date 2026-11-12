#!/usr/bin/env bash
#
# Backup diario de la base MySQL (spec 0008).
#
# Corre en el servidor, sin argumentos. Lee las credenciales del .env de la app
# y deja un .sql.gz por día en ~/backups, conservando los últimos 7.
#
# Instalación (cron del usuario):
#   15 3 * * * /home/USUARIO/alucal/deploy/backup.sh >/dev/null 2>&1

set -euo pipefail

APP_DIR="${APP_DIR:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
DESTINO="${DESTINO:-$HOME/backups}"
RETENCION="${RETENCION:-7}"

ENV_FILE="$APP_DIR/.env"
if [[ ! -f "$ENV_FILE" ]]; then
    echo "No existe $ENV_FILE" >&2
    exit 1
fi

leer() {
    # Lee una clave del .env sin ejecutar el archivo.
    grep -E "^${1}=" "$ENV_FILE" | tail -1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//'
}

DB_HOST="$(leer DB_HOST)"; DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="$(leer DB_PORT)"; DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="$(leer DB_DATABASE)"
DB_USERNAME="$(leer DB_USERNAME)"
DB_PASSWORD="$(leer DB_PASSWORD)"

if [[ -z "$DB_DATABASE" || -z "$DB_USERNAME" ]]; then
    echo "Faltan DB_DATABASE o DB_USERNAME en $ENV_FILE" >&2
    exit 1
fi

mkdir -p "$DESTINO"
ARCHIVO="$DESTINO/alucal-$(date +%Y%m%d-%H%M).sql.gz"

mysqldump \
    --host="$DB_HOST" --port="$DB_PORT" \
    --user="$DB_USERNAME" --password="$DB_PASSWORD" \
    --single-transaction --quick --skip-lock-tables --no-tablespaces \
    "$DB_DATABASE" | gzip -9 > "$ARCHIVO"

# Verificación mínima: el archivo existe y descomprime.
gzip -t "$ARCHIVO"

# Retención: se conservan los últimos N archivos.
ls -1t "$DESTINO"/alucal-*.sql.gz 2>/dev/null | tail -n +$((RETENCION + 1)) | xargs -r rm -f

printf '%s backup %s (%s)\n' "$(date '+%Y-%m-%d %H:%M')" "$(basename "$ARCHIVO")" \
    "$(du -h "$ARCHIVO" | cut -f1)" >> "$DESTINO/backup.log"
