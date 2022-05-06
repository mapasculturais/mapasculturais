#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR

MAPAS_NAME=mapas-run

docker exec -it $MAPAS_NAME bash -c "cd /var/www/html/protected/tools/ && ./doctrine orm:schema-tool:update --dump-sql"

cd $CDIR
