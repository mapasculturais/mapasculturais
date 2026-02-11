#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd "$DIR/.."

docker compose exec mapas /var/www/scripts/watch-active-theme-sass.sh

cd "$CDIR"
