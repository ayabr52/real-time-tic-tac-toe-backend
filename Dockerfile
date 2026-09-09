FROM php:8.2-fpm-alpine

# Install system dependencies & PHP extensions
RUN apk add --no-cache nginx supervisor git unzip libpng-dev libzip-dev zip sqlite-dev \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql bcmath gd zip pcntl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Create SQLite database file and set permissions
RUN touch database/database.sqlite \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Configure Nginx to reverse proxy /app to Reverb (port 8080) and all other requests to Laravel (port 10001)
RUN echo 'server { \
    listen 10000; \
    location / { \
        proxy_pass http://127.0.0.1:10001; \
        proxy_set_header Host $host; \
        proxy_set_header X-Real-IP $remote_addr; \
    } \
    location /app { \
        proxy_pass http://127.0.0.1:8080; \
        proxy_http_version 1.1; \
        proxy_set_header Upgrade $http_upgrade; \
        proxy_set_header Connection "Upgrade"; \
        proxy_set_header Host $host; \
    } \
}' > /etc/nginx/http.d/default.conf

EXPOSE 10000

CMD php artisan config:clear && \
    php artisan package:discover --ansi && \
    php artisan migrate --force && \
    php artisan reverb:start --host=0.0.0.0 --port=8080 & \
    php artisan serve --host=127.0.0.1 --port=10001 & \
    nginx -g "daemon off;"
