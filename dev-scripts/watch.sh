#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR

sudo docker exec -w /var/www/html/protected -i $(docker-compose -f docker-compose.local.yml ps -q mapas) bash -c "pnpm install --recursive && pnpm run watch"

cd $CDIR
