#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}")"/../ && pwd )"

CDIR=$(pwd)

cd $DIR

psql -c 'DROP DATABASE IF EXISTS mapasculturais_test;' -U postgres
psql -c "CREATE USER mapasculturais WITH PASSWORD 'mapasculturais'" -U postgres
psql -c 'CREATE DATABASE mapasculturais_test OWNER mapasculturais;' -U postgres
psql -c 'CREATE EXTENSION postgis;' -U postgres -d mapasculturais_test
psql -c 'CREATE EXTENSION unaccent;' -U postgres -d mapasculturais_test
psql -f db/schema.sql -U mapasculturais -d mapasculturais_test

echo "---- importing data ---"
psql -f db/test-data.sql -U mapasculturais -d mapasculturais_test

#cd src/
#echo "starting php -S on port 8081"
#MAPASCULTURAIS_CONFIG_FILE="conf-test.php" php -S 0.0.0.0:8081 &
#PID_OF_PHP=$!
#cd ..

echo 'running tests...'
src/protected/vendor/phpunit/phpunit/phpunit tests/

#echo "stopping php -S"
#kill $PID_OF_PHP
cd $CDIR
