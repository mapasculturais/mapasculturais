#!/bin/bash

export PG_SUPER_PASS="${PG_SUPER_PASS:-postgis}";
export PG_SUPER_USER="${PG_SUPER_USER:-postgres}";
export PG_DB="${PG_DB:-mapasculturais}";
export PG_PASS="${PG_PASS:-mapasculturais}";
export PG_USER="${PG_USER:-mapasculturais}";
export PG_HOST="${PG_HOST:-postgis}";
export DOMAIN="${DOMAIN:-localhost}";

#
# Configura postgre na aplicação
#
cp src/protected/application/conf/config.template.php src/protected/application/conf/config.php

doctrine_conf="'doctrine.database'=>[";
doctrine_conf="$doctrine_conf 'dbname'=>'$PG_DB',";
doctrine_conf="$doctrine_conf 'password'=>'$PG_PASS',";
doctrine_conf="$doctrine_conf 'user'=>'$PG_USER',";
doctrine_conf="$doctrine_conf 'host'=>'$PG_HOST',";
doctrine_conf="$doctrine_conf ]";

su mapas -c sh << SUBSCRIPT
sed -i -z -e "s/'doctrine.database'[^]]*\]/$doctrine_conf/" src/protected/application/conf/config.php
SUBSCRIPT

#
# Popula banco no primeiro acesso
#
if ! PGPASSWORD="$PG_PASS" psql -q -U "$PG_USER" -h "$PG_HOST" -d "$PG_DB" -w -c '\q' 2>&-;
then
    PGPASSWORD="$PG_SUPER_PASS" psql -U "$PG_SUPER_USER" -h "$PG_HOST" -w -c "CREATE USER $PG_USER WITH PASSWORD '$PG_PASS';"
    PGPASSWORD="$PG_SUPER_PASS" psql -U "$PG_SUPER_USER" -h "$PG_HOST" -w -c "CREATE DATABASE $PG_DB OWNER $PG_USER;"
    PGPASSWORD="$PG_SUPER_PASS" psql -U "$PG_SUPER_USER" -h "$PG_HOST" -w -d $PG_DB -c "CREATE EXTENSION postgis;"
    PGPASSWORD="$PG_SUPER_PASS" psql -U "$PG_SUPER_USER" -h "$PG_HOST" -w -d $PG_DB -c "CREATE EXTENSION unaccent;"

    PGPASSWORD="$PG_PASS" psql -U "$PG_USER" -h "$PG_HOST" -w -f db/schema.sql
    PGPASSWORD="$PG_PASS" psql -U "$PG_USER" -h "$PG_HOST" -w -f db/initial-data.sql
fi;


if mount | grep '/srv/mapas/mapasculturais';
then
su mapas -c sh << SUBSCRIPT
    # Reinstall deps if needed
    cd src/protected;
    composer -n install --prefer-dist;
    composer -n dump-autoload --optimize;
SUBSCRIPT
fi

su mapas -c sh << SUBSCRIPT
    # Apply database modifications
    cd src/protected/tools;
    HTTP_HOST=$DOMAIN REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' SERVER_NAME=127.0.0.1 SERVER_PORT="8000" ./doctrine orm:generate-proxies;
    HTTP_HOST=$DOMAIN REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' SERVER_NAME=127.0.0.1 SERVER_PORT="8000" php apply-updates.php 0;
SUBSCRIPT

ASSETS_FOLDER=$(HTTP_HOST=$DOMAIN REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' SERVER_NAME=127.0.0.1 SERVER_PORT="8000" php src/protected/tools/get-theme-assets-path.php)

su mapas -c sh << SUBSCRIPT
    # compile assets
    cd scripts/;
    ./compile-sass.sh;
    touch /tmp/test
SUBSCRIPT

#
# Executa o que está em CMD no Dockerfile
#
exec "$@"

