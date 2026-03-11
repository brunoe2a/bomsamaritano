# ============================================================
# Stage 1: Build Frontend Assets (Node.js)
# ============================================================
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json* pnpm-lock.yaml* ./

# Install dependencies (npm or pnpm)
RUN if [ -f pnpm-lock.yaml ]; then \
        npm install -g pnpm && pnpm install --frozen-lockfile; \
    else \
        npm ci; \
    fi

COPY . .

RUN npm run build

# ============================================================
# Stage 2: PHP Application
# ============================================================
FROM php:8.3-fpm-alpine AS app

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    zip \
    unzip \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    libxml2-dev \
    sqlite-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_sqlite \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
    && rm -rf /var/cache/apk/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies (production only)
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# Copy the rest of the application
COPY . .

# Copy built frontend assets from Stage 1
COPY --from=frontend /app/public/build public/build

# Run post-install scripts
RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi

# Create necessary directories
RUN mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    database

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache database

# ============================================================
# Nginx Configuration
# ============================================================
RUN rm -f /etc/nginx/http.d/default.conf
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# ============================================================
# PHP-FPM Configuration
# ============================================================
COPY docker/php.ini /usr/local/etc/php/conf.d/99-custom.ini

# ============================================================
# Supervisor Configuration
# ============================================================
COPY docker/supervisord.conf /etc/supervisord.conf

# ============================================================
# Entrypoint Script
# ============================================================
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
