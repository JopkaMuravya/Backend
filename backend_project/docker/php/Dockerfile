FROM php:8.3-fpm

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    libpq-dev \
    postgresql-client

# Установка расширений PHP
RUN docker-php-ext-install pdo pdo_pgsql

# Рабочая директория
WORKDIR /var/www/project

# Копируем всё
COPY . .

EXPOSE 9000
CMD ["php-fpm"]