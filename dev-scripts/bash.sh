#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR

sudo docker exec -it $(docker-compose -f docker-compose.local.yml ps -q mapas) bash

cd $CDIR