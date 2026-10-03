#!/bin/bash
set -Eeuo pipefail
cd /var/www

export PORT="${PORT:-10000}"

if [[ -n "${MYSQL_ATTR_SSL_CA:-}" ]]; then
    if [ ! -f "$MYSQL_ATTR_SSL_CA" ] || [ ! -r "$MYSQL_ATTR_SSL_CA" ]; then
        echo "Cannot read MySQL CA file." >&2
        exit 1
    fi
    umask 077
    mkdir -p /run/app-certificates
    chown root:www-data /run/app-certificates
    chmod 750 /run/app-certificates
    cp "$MYSQL_ATTR_SSL_CA" /run/app-certificates/mysql-ca.pem
    chown www-data:www-data /run/app-certificates/mysql-ca.pem
    chmod 400 /run/app-certificates/mysql-ca.pem
    export MYSQL_ATTR_SSL_CA=/run/app-certificates/mysql-ca.pem
    su-exec www-data php docker/check-ca.php || true
fi

if (($# > 0)); then
    exec su-exec www-data "$@"
fi

: "${APP_KEY:?Set APP_KEY}"

envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf
mkdir -p storage/framework/{cache/data,sessions,views} storage/logs storage/app/public bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Always use runtime environment values such as APP_KEY and APP_URL.
su-exec www-data php artisan config:clear
su-exec www-data php artisan route:clear
su-exec www-data php artisan view:clear

php-fpm -D
exec nginx -g 'daemon off;'