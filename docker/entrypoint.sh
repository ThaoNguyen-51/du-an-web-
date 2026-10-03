#!/bin/bash
set -Eeuo pipefail
cd /var/www

: "${APP_KEY:?Set APP_KEY}"
export PORT="${PORT:-10000}"

envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf
mkdir -p storage/framework/{cache/data,sessions,views} storage/logs storage/app/public bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Xóa cache cấu hình cũ
su-exec www-data php artisan config:clear || true
su-exec www-data php artisan route:cache || true
su-exec www-data php artisan view:cache || true

# TỰ ĐỘNG CHẠY MIGRATION NẾU CÓ BIẾN RUN_MIGRATIONS=true
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Running database migrations..."
    su-exec www-data php artisan migrate --force || true
fi

php-fpm -D
nginx -g 'daemon off;'