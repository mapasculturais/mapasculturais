#!/bin/bash
set -e

if [ ! -f /.deployed ]; then
    cd /var/www/scripts
    ./deploy.sh
    touch /.deployed
fi

exec "$@"
