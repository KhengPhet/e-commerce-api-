# ============================================
# Laravel 10 API - Railway Deployment
# PHP 8.2 + MySQL + KHQR (simple-qrcode)
# NOTE: PHP 8.2 required by lcobucci/clock 2.3.0
#       (dependency of lcobucci/jwt -> tymon/jwt-auth)
# ============================================
FROM php:8.2-cli

# ---------- System dependencies ----------
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        curl \
        zip \
        unzip \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# ---------- PHP extensions ----------
# gd: required by simplesoftwareio/simple-qrcode
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        gd \
        pdo_mysql \
        bcmath \
        exif \
        pcntl \
        zip

# redis extension via PECL
# NOTE: mbstring is already built into official php:* images (check: php -m | grep mbstring)
RUN pecl install redis && docker-php-ext-enable redis

# ---------- PHP settings ----------
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "upload_max_filesize=10M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=12M" >> /usr/local/etc/php/conf.d/uploads.ini

# ---------- Composer ----------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# ---------- Install vendor deps first (better layer caching) ----------
COPY composer.json composer.lock ./

RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction \
        --no-progress

# ---------- Copy application code ----------
COPY . .

RUN composer dump-autoload --optimize

# ---------- Permissions ----------
# storage + bootstrap/cache must be writable; public/images for product uploads
RUN mkdir -p public/images/products storage/framework/views \
    && chown -R www-data:www-data storage bootstrap/cache public/images \
    && chmod -R ug+rwX storage bootstrap/cache public/images

USER www-data

EXPOSE 8080

# ---------- Start on Railway's $PORT ----------
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
