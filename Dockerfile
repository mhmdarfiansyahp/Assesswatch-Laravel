FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev libzip-dev cron supervisor \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing app
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Supervisor config (for queue workers)
RUN mkdir -p /etc/supervisor/conf.d
COPY --chown=www-data:www-data . /var/www

# Expose php-fpm
EXPOSE 9001

CMD [ "php-fpm" ]