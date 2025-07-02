FROM php:8.2-fpm

# Installer les extensions nécessaires
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql

WORKDIR /app
COPY . /app

# Configurer PHP-FPM pour écouter sur 0.0.0.0:9000
RUN echo "listen = 0.0.0.0:9000" >> /usr/local/etc/php-fpm.d/www.conf

# Exposer le port pour PHP-FPM
EXPOSE 9000

# Pour Render: configurer une commande pour démarrer PHP-FPM en avant-plan
CMD ["php-fpm", "--nodaemonize"]
