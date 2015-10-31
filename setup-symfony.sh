#!/bin/bash
mkdir -p /var/www/ttapp
cd /var/www/ttapp
export COMPOSER_HOME="/root"
export SYMFONY_ENV=prod
scl enable php55 '/bin/composer install --no-dev --optimize-autoloader --no-interaction' &>> /tmp/cd_debug
scl enable php55 'php /var/www/ttapp/app/console cache:clear --env=prod --no-debug' &>> /tmp/cd_debug
chown -R nginx:nginx /var/www/ttapp/app/logs /var/www/ttapp/app/cache; 
