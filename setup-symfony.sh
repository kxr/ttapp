#!/bin/bash
mkdir -p /var/www/ttapp
cd /var/www/ttapp
export COMPOSER_HOME="/root"
export SYMFONY_ENV=prod
/bin/composer install --no-dev --optimize-autoloader --no-interaction
scl enable php55 '/usr/bin/php /var/www/ttapp/app/console cache:clear --env=prod --no-debug'
chown -R nginx:nginx /var/www/ttapp/app/logs /var/www/ttapp/app/cache; 
