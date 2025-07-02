#!/bin/bash
set -e

# Remplace PORT dans la configuration d'Apache
if [ -n "$PORT" ]; then
  echo "Configuring Apache to listen on port $PORT"
  sed -i "s/Listen \${PORT:-80}/Listen $PORT/g" /etc/apache2/ports.conf
  sed -i "s/:\${PORT:-80}/\:$PORT/g" /etc/apache2/sites-available/000-default.conf
fi

# Installation des dépendances avec Composer si vendor n'existe pas
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
  echo "Installing Composer dependencies..."
  composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction
fi

# Vérifie si le fichier d'autoload existe
if [ ! -f "vendor/autoload_runtime.php" ]; then
  echo "Generating autoloader..."
  composer dump-autoload --optimize
fi

# Assure que les permissions sont correctes
echo "Setting file permissions..."
chmod -R 777 /var/www/html/var

# Définit APP_ENV=prod si non défini
if [ -z "$APP_ENV" ]; then
  export APP_ENV=prod
fi

echo "Starting Apache..."
# Lance la commande passée en argument
exec "$@"
