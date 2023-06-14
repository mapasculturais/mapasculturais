#!/bin/bash
set -e
set -o pipefail


if [ $1 ]; then
	DOMAIN=$1
else
	DOMAIN=localhost
fi


if [ -f /tmp/mapasculturais-tests-authenticated-user.id ]
then
	rm "/tmp/mapasculturais-tests-authenticated-user.id"
fi

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}")"/../ && pwd )"

CDIR=$(pwd)

if [ -f /tmp/mapasculturais-tests-authenticated-user.id ]
then
	rm "/tmp/mapasculturais-tests-authenticated-user.id"
fi

cd $DIR/scripts
psql -c 'DROP DATABASE IF EXISTS mapasculturais_test;' -U postgres
psql -c 'DROP USER IF EXISTS mapasculturais_test;' -U postgres
psql -c "CREATE USER mapasculturais_test WITH PASSWORD 'mapasculturais_test' SUPERUSER" -U postgres
./restore-dump.sh -o=mapasculturais_test -f=db-test.sql -db=mapasculturais_test --noconfirm
./compile-sass.sh localhost conf-test.php
./db-update.sh localhost 0 conf-test.php
./mc-db-updates.sh -c="conf-test.php" -p=1

cd ..

#echo "---- importing data ---"
#psql -f db/test-data.sql -U mapasculturais_test -d mapasculturais_test


cd src/

echo "starting php -S on port 8888"

MAPASCULTURAIS_CONFIG_FILE="conf-test.php" php -d variables_order=EGPCS -S 0.0.0.0:8888&
PID_OF_PHP=$!
cd ..

echo 'running tests...'
src/protected/vendor/phpunit/phpunit/phpunit tests/

echo "stopping php -S"
kill $PID_OF_PHP
cd $CDIR
