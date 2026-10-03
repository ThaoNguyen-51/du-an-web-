FROM php:8.2-fpm-alpine AS php-base

RUN apk add --no-cache \
    bash \
    nginx \
    curl \
    gettext \
    su-exec \
    tini \
    ca-certificates \
    libpng \
    libzip \
    oniguruma

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS libpng-dev libzip-dev oniguruma-dev \
    && docker-php-ext-install pdo_mysql mbstring zip gd bcmath opcache \
    && apk del .build-deps

WORKDIR /var/www

FROM php-base AS composer-stage
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY . .

RUN mkdir -p bootstrap/cache storage/framework/cache/data \
    storage/framework/sessions storage/framework/views storage/logs storage/app/public \
    && composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

FROM php-base AS production
ENV APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr LOG_LEVEL=info \
    DB_CONNECTION=mysql SESSION_DRIVER=database SESSION_SECURE_COOKIE=true \
    CACHE_STORE=database QUEUE_CONNECTION=sync PORT=10000 RUN_MIGRATIONS=true
COPY --from=composer-stage --chown=www-data:www-data /var/www /var/www
COPY docker/nginx.conf /etc/nginx/templates/default.conf.template
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/app-entrypoint
RUN mkdir -p /run/nginx \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R ug+rwX /var/www/storage /var/www/bootstrap/cache
EXPOSE 10000
HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD curl --fail-silent "http://127.0.0.1:${PORT}/up" > /dev/null || exit 1
ENTRYPOINT ["/sbin/tini", "--", "/usr/local/bin/app-entrypoint"]