#!/bin/bash

#
# Credenciais do superuser no postgre
#
if [ -z "$PG_SUPER_PASS" ];
then
    export PG_SUPER_PASS="postgis";
fi;

if [ -z "$PG_SUPER_USER" ];
then
    export PG_SUPER_USER="postgres";
fi;

#
# Dados do mapasculturais no postgre
#
if [ -z "$PG_DB" ];
then
    export PG_DB="mapasculturais";
fi;

if [ -z "$PG_PASS" ];
then
    export PG_PASS="mapasculturais";
fi;

if [ -z "$PG_USER" ];
then
    export PG_USER="mapasculturais";
fi;

#
# Host (container) em que roda postgres (--link postgis:postgis)
#
if [ -z "$PG_HOST" ];
then
    export PG_HOST="postgis";
fi;

#
# Domínio da aplicação
#
if [ -z "$DOMAIN" ];
then
    export DOMAIN="localhost";
fi;

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

    (
     cd src/protected/tools;
     HTTP_HOST=$DOMAIN REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' SERVER_NAME=127.0.0.1 SERVER_PORT="8000" ./doctrine orm:generate-proxies;
     HTTP_HOST=$DOMAIN REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' SERVER_NAME=127.0.0.1 SERVER_PORT="8000" php apply-updates.php 0;
    )

    ASSETS_FOLDER=$(HTTP_HOST=$DOMAIN REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' SERVER_NAME=127.0.0.1 SERVER_PORT="8000" php src/protected/tools/get-theme-assets-path.php)
    sass $ASSETS_FOLDER/css/sass/main.scss:$ASSETS_FOLDER/css/main.css -E "UTF-8"
fi;

#
# Executa o que está em CMD no Dockerfile
#
exec "$@"

