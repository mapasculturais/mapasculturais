#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd "$DIR/.."

docker compose exec mapas bash -c "cd /var/www/src/tools/ && ./doctrine orm:schema-tool:update --dump-sql"

cd "$CDIR"
