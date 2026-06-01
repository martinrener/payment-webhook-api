# 1. Imagen base
FROM php:8.4-fpm

# 2. Dependencias del sistema
RUN apt-get update && apt-get install -y libonig-dev zip unzip git nginx

# 3. Extensiones PHP
RUN docker-php-ext-install pdo_mysql mbstring pcntl

# 4. Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Código
WORKDIR /var/www/html
COPY . .

# 6. Dependencias
RUN composer install --no-dev

# 7. Permisos
RUN chown -R www-data:www-data storage bootstrap/cache

# 8. Entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["docker-entrypoint.sh"]

# 9. Arranque
CMD ["php-fpm"]