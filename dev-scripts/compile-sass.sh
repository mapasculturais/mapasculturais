#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR

sudo docker exec -i $(docker-compose -f docker-compose.local.yml ps -q mapas) "/var/www/scripts/compile-sass.sh"

cd $CDIR
