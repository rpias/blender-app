#!/usr/bin/env bash
#
# Publica Alucal en el hosting por SSH.
#
# Uso:
#   SSH_HOST=srv1595.hstgr.io SSH_PORT=65002 SSH_USER=u123456789 \
#   APP_DIR=~/alucal WEB_DIR=~/domains/ejemplo.com/public_html \
#   ./deploy/publicar.sh
#
# Requisitos locales: rsync, ssh, php, composer.
# El servidor necesita php (cli) en el PATH y, para migrar, acceso a MySQL.

set -euo pipefail

SSH_HOST="${SSH_HOST:-185.173.111.90}"
SSH_PORT="${SSH_PORT:-65002}"
SSH_KEY="${SSH_KEY:-$HOME/.ssh/alucal_hostinger}"
SSH_USER="${SSH_USER:-u746296444}"
APP_DIR="${APP_DIR:-/home/u746296444/alucal}"
WEB_DIR="${WEB_DIR:-/home/u746296444/domains/springgreen-mole-850582.hostingersite.com/public_html}"
PHP_BIN="${PHP_BIN:-php}"

RAIZ="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SSH_OPTS=(-i "$SSH_KEY" -p "$SSH_PORT" -o BatchMode=yes -o StrictHostKeyChecking=accept-new)

echo "==> Verificando conexión SSH"
ssh "${SSH_OPTS[@]}" "${SSH_USER}@${SSH_HOST}" "echo conectado; ${PHP_BIN} -v | head -1"

echo "==> Preparando dependencias de producción en local"
composer install --no-dev --optimize-autoloader --no-interaction --quiet

echo "==> Sincronizando aplicación a ${APP_DIR}"
rsync -az --delete \
  -e "ssh ${SSH_OPTS[*]}" \
  --exclude '.git' \
  --exclude 'node_modules' \
  --exclude 'tests' \
  --exclude '.env' \
  --exclude 'database/database.sqlite' \
  --exclude 'storage/app/alucal-datos.sql' \
  --exclude 'storage/framework/cache/*' \
  --exclude 'storage/framework/sessions/*' \
  --exclude 'storage/framework/views/*' \
  --exclude 'storage/logs/*' \
  --exclude 'bootstrap/cache/*.php' \
  "${RAIZ}/" "${SSH_USER}@${SSH_HOST}:${APP_DIR}/"

if [[ -n "$WEB_DIR" ]]; then
  echo "==> public_html apunta a ${APP_DIR}/public (symlink); sin copia"
fi

echo "==> Migrando y cacheando en el servidor"
ssh "${SSH_OPTS[@]}" "${SSH_USER}@${SSH_HOST}" bash -s <<EOF
set -e
cd ${APP_DIR}
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
# nunca reusar cachés de paquetes generados en otra máquina (p. ej. con dependencias --dev)
rm -f bootstrap/cache/*.php
# los archivos estáticos los sirve el usuario del web server: tienen que ser legibles
find public -type d -exec chmod 755 {} \;
find public -type f -exec chmod 644 {} \;
chmod -R ug+rwX storage bootstrap/cache
${PHP_BIN} artisan migrate --force
${PHP_BIN} artisan config:cache
${PHP_BIN} artisan route:cache
${PHP_BIN} artisan view:cache
${PHP_BIN} artisan alucal:recalcular || true
EOF

echo "==> Listo"
