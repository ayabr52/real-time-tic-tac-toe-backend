FROM php:8.2-fpm-alpine

# Install system dependencies & PHP extensions
RUN apk add --no-cache nginx supervisor git unzip libpng-dev libzip-dev zip \
    && docker-php-ext-install pdo pdo_mysql bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Install PHP dependencies without running post-install scripts
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 10000 8080

CMD php artisan package:discover --ansi && \
    php artisan migrate --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan serve --host 0.0.0.0 --port 10000 & \
    php artisan reverb:start --host 0.0.0.0 --port 8080
