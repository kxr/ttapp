#!/bin/bash

ELB_DBAM_DNS=$(aws elb describe-load-balancers --load-balancer-names ilb-dbam | jq -r '.LoadBalancerDescriptions[].DNSName')
ELB_DBNZ_DNS=$(aws elb describe-load-balancers --load-balancer-names ilb-dbnz | jq -r '.LoadBalancerDescriptions[].DNSName')

cd /var/www/ttapp
sed -i "s/ilb-dbam-UPDATEME/$ELB_DBAM_DNS/" app/config/parameters.yml.dist
sed -i "s/ilb-dbnz-UPDATEME/$ELB_DBNZ_DNS/" app/config/parameters.yml.dist

export COMPOSER_HOME="/root"
export SYMFONY_ENV=prod
scl enable php55 '/bin/composer install --no-dev --optimize-autoloader --no-interaction' &>> /tmp/cd_debug
scl enable php55 'php /var/www/ttapp/app/console cache:clear --env=prod --no-debug' &>> /tmp/cd_debug
chown -R nginx:nginx /var/www/ttapp/app/logs /var/www/ttapp/app/cache; 
