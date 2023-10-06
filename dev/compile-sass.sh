#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR

docker-compose exec "/var/www/scripts/compile-sass.sh"

cd $CDIR
