#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}")"/../ && pwd )"

CDIR=$(pwd)

cd $DIR

psql -c 'DROP DATABASE IF EXISTS mapasculturais_test;' -U postgres
psql -c 'CREATE DATABASE mapasculturais_test OWNER mapasculturais;' -U postgres
psql -c 'CREATE EXTENSION postgis;' -U postgres -d mapasculturais_test
psql -c 'CREATE EXTENSION unaccent;' -U postgres -d mapasculturais_test
psql -f db/schema.sql -U mapasculturais -d mapasculturais_test
psql -f db/test-data.sql -U mapasculturais -d mapasculturais_test


tar xf db/sp-shapefile-sql.tar.xz

psql -c "DROP table IF EXISTS sp_regiao;" -U mapasculturais -d mapasculturais_test
psql -f sp-shapefile-sql/sp_regiao.sql -U mapasculturais -d mapasculturais_test
psql -c "DROP table IF EXISTS sp_distrito;" -U mapasculturais -d mapasculturais_test
psql -f sp-shapefile-sql/sp_distrito.sql -U mapasculturais -d mapasculturais_test
psql -c "DROP table IF EXISTS sp_subprefeitura;" -U mapasculturais -d mapasculturais_test
psql -f sp-shapefile-sql/sp_subprefeitura.sql -U mapasculturais -d mapasculturais_test

rm -rf sp-shapefile-sql

cd src/

echo "starting php -S on port 8081"
MAPASCULTURAIS_CONFIG_FILE="conf-test.php" php -S 0.0.0.0:8081 &
PID_OF_PHP=$!
cd ..

echo 'running tests...'
src/protected/vendor/phpunit/phpunit/phpunit tests/

echo "stopping php -S"
kill $PID_OF_PHP
cd $CDIR
