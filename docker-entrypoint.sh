#!/bin/bash
set -e

# Remplace PORT dans la configuration d'Apache
if [ -n "$PORT" ]; then
  echo "Configuring Apache to listen on port $PORT"
  sed -i "s/Listen \${PORT:-80}/Listen $PORT/g" /etc/apache2/ports.conf
  sed -i "s/:\${PORT:-80}/\:$PORT/g" /etc/apache2/sites-available/000-default.conf
fi

# Assure que les permissions sont correctes
chmod -R 777 /var/www/html/var

# Lance la commande passée en argument
exec "$@"
