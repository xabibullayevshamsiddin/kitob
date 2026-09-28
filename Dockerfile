# ========================================================
# Stage 1: Build Frontend Assets (Vite + Tailwind CSS)
# ========================================================
FROM node:20-alpine AS frontend
WORKDIR /app

COPY package*.json ./
RUN npm ci || npm install

COPY resources ./resources
COPY public ./public
COPY vite.config.js tailwind.config.js postcss.config.js ./

RUN npm run build

# ========================================================
# Stage 2: Install PHP Composer Dependencies
# ========================================================
FROM composer:2 AS composer
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-scripts \
    --ignore-platform-reqs \
    --optimize-autoloader

# ========================================================
# Stage 3: Production PHP 8.2 + Nginx Environment
# ========================================================
FROM php:8.2-fpm-alpine

LABEL maintainer="Kitobxon Platform"

# Install system dependencies & build packages
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    bash \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    postgresql-dev \
    mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# Copy custom configs
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Ensure Linux LF line endings and executable permission
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && \
    chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

# Copy application source code
COPY . .

# Copy built frontend assets from Stage 1
COPY --from=frontend /app/public/build ./public/build

# Copy PHP dependencies from Stage 2
COPY --from=composer /app/vendor ./vendor

# Copy composer binary to dump autoload
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer dump-autoload --optimize --no-dev && \
    rm /usr/bin/composer

# Setup directories and permissions
RUN mkdir -p /var/www/html/storage/framework/cache/data \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/logs \
             /var/www/html/storage/app/public && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Render assigns port dynamically via $PORT (defaults to 10000 or 80)
EXPOSE 80 10000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
