FROM php:8.2-apache

# Installer les extensions nécessaires
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip opcache

# Activer le module rewrite d'Apache
RUN a2enmod rewrite

# Installer Composer - méthode officielle
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier tout le code source
COPY . .

# Configurer Apache pour utiliser le répertoire public de Symfony comme DocumentRoot
RUN sed -i -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf
RUN sed -i -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Configurer Apache pour écouter sur 0.0.0.0:PORT où PORT sera défini par l'environnement Render
RUN sed -i 's/Listen 80/Listen ${PORT:-80}/g' /etc/apache2/ports.conf
RUN sed -i 's/:80/:${PORT:-80}/g' /etc/apache2/sites-available/000-default.conf

# Changer les permissions pour les dossiers de cache et de logs
RUN mkdir -p /var/www/html/var/cache /var/www/html/var/log \
    && chmod -R 777 /var/www/html/var

# Exposer le port sur lequel Apache écoutera
EXPOSE ${PORT:-80}

# Script de démarrage pour lire PORT de l'environnement Render
COPY --chmod=755 ./docker-entrypoint.sh /usr/local/bin/docker-entrypoint
ENTRYPOINT ["docker-entrypoint"]
CMD ["apache2-foreground"]
