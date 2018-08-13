#!/bin/bash
set -e

if [ ! -f /.deployed ]; then
    cd /var/www/scripts
    ./deploy.sh
    touch /.deployed
fi

chown -R www-data:www-data /var/www/html/assets /var/www/html/files /var/www/private-files

exec "$@"
