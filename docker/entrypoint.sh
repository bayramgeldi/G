#!/usr/bin/env bash
set -euo pipefail

if [ -z "${APP_KEY:-}" ]; then
  [ -f .env ] || cp .env.example .env
  php artisan key:generate --force --no-interaction
fi

php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
