FROM php:8.2-apache

# System deps + PHP extensions:
# - pdo_pgsql/pgsql: PostgreSQL (Render's free managed DB)
# - gd: required for the PNG results export (ExportController::image)
# - zip: required by maatwebsite/excel
RUN apt-get update && apt-get install -y \
    libpq-dev libpng-dev libjpeg-dev libfreetype6-dev libzip-dev unzip git \
    fonts-noto-cjk \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install pdo_pgsql pgsql gd zip \
    && a2enmod rewrite
WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

# Point Apache at Laravel's /public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

# storage:link must be recreated on every boot — Render spins up a fresh
# container per deploy, so anything not in the repo (including symlinks) is gone.
CMD php artisan storage:link --force \
    && php artisan migrate --force \
    && php artisan db:seed --force \
    && apache2-foreground
