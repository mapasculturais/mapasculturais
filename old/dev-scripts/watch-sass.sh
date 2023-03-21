#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR

MAPAS_NAME=mapas-run

sudo docker exec -i $MAPAS_NAME "/var/www/scripts/watch-active-theme-sass.sh"

cd $CDIR
