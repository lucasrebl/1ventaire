FROM php:8.2-fpm

# Installer les extensions nécessaires
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install

WORKDIR /app
COPY . /app

