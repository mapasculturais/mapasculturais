#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd "$DIR/.."

docker compose exec mapas sh /var/www/scripts/shell.sh

cd "$CDIR"
