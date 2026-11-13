FROM composer:2 AS composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

COPY . .
RUN php artisan package:discover --ansi

FROM node:22-bookworm-slim AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY vite.config.js ./
COPY --from=composer /app/vendor ./vendor
RUN npm run build

FROM php:8.4-apache-bookworm AS runtime

ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_MAINTENANCE_DRIVER=cache \
    APP_MAINTENANCE_STORE=database \
    DB_CONNECTION=pgsql \
    DB_SSLMODE=prefer \
    SESSION_DRIVER=database \
    SESSION_ENCRYPT=true \
    SESSION_SECURE_COOKIE=true \
    SESSION_HTTP_ONLY=true \
    SESSION_SAME_SITE=lax \
    CACHE_STORE=database \
    QUEUE_CONNECTION=sync \
    LOG_CHANNEL=stderr \
    LOG_LEVEL=info \
    PORT=10000

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libpq-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        gd \
        intl \
        mbstring \
        opcache \
        pdo_pgsql \
        zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=composer --chown=www-data:www-data /app /var/www/html
COPY --from=assets --chown=www-data:www-data /app/public/build /var/www/html/public/build
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf.template
COPY docker/start-render.sh /usr/local/bin/start-render

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod +x /usr/local/bin/start-render

EXPOSE 10000

CMD ["start-render"]
