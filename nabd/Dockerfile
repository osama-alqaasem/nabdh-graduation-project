FROM php:8.2-fpm

# تثبيت المتطلبات
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# نسخ المشروع
COPY . .

# تثبيت Laravel dependencies
RUN composer install

# صلاحيات
RUN chown -R www-data:www-data /var/www

EXPOSE 8080

CMD php artisan serve --host=0.0.0.0 --port=8080