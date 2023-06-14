#!/bin/bash
#set -e                      #Exit immediately if a command exits with a non-zero status.
set -o pipefail

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}")"/../ && pwd )"
CDIR=$(pwd)
FILE="db/db-test.sql"
FILE_CONF_TEST="$DIR/src/protected/application/conf/conf-test-local.php"

echo $FILE_CONF_TEST

_NOME_BASE_TEST="$( cat $FILE_CONF_TEST | grep "'dbname'" | perl -pe 's|\s*.dbname.\s*=>\s*.(.*?)\W\s*,.*|$1|' )"
_USER="$( cat $FILE_CONF_TEST | grep "'user'" | perl -pe 's|\s*.user.\s*=>\s*.(.*?)\W\s*,.*|$1|' )"
_HOST="$( cat $FILE_CONF_TEST | grep "'host'" | perl -pe 's|\s*.host.\s*=>\s*.(.*?)\W\s*,.*|$1|' )"
_PORT="5432"
_CREATE_DB=false

for i in "$@"
do
    case $i in
        --createdb)
            _CREATE_DB=true
            shift
        ;;
        -u=*|--user=*)
            _USER="${i#*=}"
            shift # past argument=value
        ;;
        -h=*|--host=*)
            _HOST="${i#*=}"
            shift # past argument=value
        ;;
        -p=*|--port=*)
            _PORT="${i#*=}"
            shift # past argument=value
        ;;        
        --help)
            echo "
        runt-test-local.sh [-u=postgres] [-h=localhost] [-p=5432] [-o=1]

        -u=  | --user =  usuário padrão para conectar a base do postgresql. (padrão: mapas)
        -h=  | --host =	 endereço do servidor do postgresql. (padrão: localhost)
        -p=  | --port =	 porta do servidor postgresql (padrão: 5432)
           --createdb =  criar a base de dados mapasculturais_test antes de executar os testes
            "
            exit
        ;;
    esac
done

if [ -f /tmp/mapasculturais-tests-authenticated-user.id ]
then
    rm "/tmp/mapasculturais-tests-authenticated-user.id"
fi

echo "BASE TEST: $_NOME_BASE_TEST, USER: $_USER, HOST: $_HOST"

if [ $_CREATE_DB == true ]
then
    echo "Password for user $_USER dropdb:\n"
    dropdb   -h $_HOST -p $_PORT -U $_USER --if-exists $_NOME_BASE_TEST
    echo "Password for user $_USER createdb:\n"
    createdb -h $_HOST -p $_PORT -U $_USER $_NOME_BASE_TEST --owner $_USER
    psql     -h $_HOST -p $_PORT -U $_USER -d $_NOME_BASE_TEST < "$DIR/$FILE"
fi

./compile-sass.sh localhost conf-test-local.php
./db-update.sh localhost 0 conf-test-local.php
./mc-db-updates.sh -c="conf-test-local.php" -p=1

cd $DIR/src/
echo "starting php -S on port 8888"

MAPASCULTURAIS_CONFIG_FILE="conf-test-local.php" php -d variables_order=EGPCS -S 0.0.0.0:8888 &
PID_OF_PHP=$!
cd ..
echo 'running tests...'
MAPASCULTURAIS_CONFIG_FILE="conf-test-local.php" src/protected/vendor/phpunit/phpunit/phpunit tests/

echo "stopping php -S"
kill $PID_OF_PHP
cd $CDIR