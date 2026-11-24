#!/usr/bin/env bash
set -e

mkdir -p /app/storage/database \
         /app/storage/framework/cache/data \
         /app/storage/framework/sessions \
         /app/storage/framework/views \
         /app/storage/logs

chmod -R 775 /app/storage
touch /app/storage/database/database.sqlite

php artisan migrate --seed --force
php artisan config:clear
php artisan route:clear
php artisan view:clear

export APP_DEBUG=true
export LOG_CHANNEL=stderr
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
