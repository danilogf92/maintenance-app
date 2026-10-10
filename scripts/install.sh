#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
docker version
[ -f .env ] || cp .env.example .env
docker compose build app
docker compose up -d mariadb
docker compose run --rm app composer install --no-interaction --prefer-dist
if grep -Eq '^APP_KEY=[[:space:]]*$' .env; then docker compose run --rm app php artisan key:generate; fi
docker compose run --rm app php artisan migrate --seed --force
docker compose run --rm app php artisan storage:link
docker compose --profile tools up -d
printf '%s\n' 'Crear SuperAdmin: docker compose exec app php artisan template:setup su-correo@empresa.com' 'Aplicación: http://localhost:8080'
