# Build frontend assets using the committed npm lockfile.
FROM node:24-alpine AS frontend

WORKDIR /build
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./
COPY public ./public
ARG VITE_APP_NAME="Desa Digital"
ENV VITE_APP_NAME=${VITE_APP_NAME}
RUN npm run build

# Laravel PHP-FPM application image.
FROM php:8.5-fpm-bookworm AS app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/zz-uploads.ini

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        exif \
        gd \
        intl \
        mbstring \
        pdo_mysql \
        pcntl \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . .
RUN composer install \
        --no-dev \
        --no-interaction \
        --prefer-dist \
        --no-progress \
        --no-scripts \
        --optimize-autoloader \
    && php artisan package:discover --ansi \
    && mkdir -p storage/app/private storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && ln -s ../storage/app/public public/storage \
    && chown -R www-data:www-data storage bootstrap/cache

COPY --from=frontend /build/public/build ./public/build
COPY docker/entrypoint.sh /usr/local/bin/desa-digital-entrypoint
RUN chmod +x /usr/local/bin/desa-digital-entrypoint

ENTRYPOINT ["/usr/local/bin/desa-digital-entrypoint"]
CMD ["php-fpm"]

# Nginx serves the public assets from the same source revision as the app image.
FROM nginx:1.28-alpine AS web

COPY --from=app /var/www/html/public /var/www/html/public
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
