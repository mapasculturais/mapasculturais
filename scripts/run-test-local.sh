#!/bin/bash
set -e                      #Exit immediately if a command exits with a non-zero status.
set -o pipefail

_USER="mapas"
_HOST="localhost"
_PORT="5432"
_OPTION=0

for i in "$@"
do
    case $i in
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
        -o=*|--option=*)
            _OPTION="${i#*=}"
            shift # past argument=value
        ;;
        --help)
            echo "
        runt-test-local.sh [-u=postgres] [-h=localhost] [-p=5432] [-o=1]

        -u=  | --user= 	 usuário padrão para conectar a base do postgresql. (padrão: mapas)
        -h=  | --host=	 endereço do servidor do postgresql. (padrão: localhost)
        -p=  | --port=	 porta do servidor postgresql (padrão: 5432)
        -o=  | --option = [0, 1 ou 2] (padrão: 0)
            0 - executa os scripts para criação da base de dados de teste e os arquivos executaveis (atualização da base e testes unitários)  (padrão: 0)
            1 - executa somente os scripts para criação da base de dados de teste.
            2 - executa arquivos (atualização da base e testes unitários).
            "
            exit
        ;;
    esac
done


if [ -f /tmp/mapasculturais-tests-authenticated-user.id ] #FOR TRAVIS-CI?
then
    rm "/tmp/mapasculturais-tests-authenticated-user.id"
fi

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}")"/../ && pwd )"
CDIR=$(pwd)
FILE="db/db-test.sql"

if [ $_OPTION -eq 0 ] || [ $_OPTION -eq 1 ]
then
    psql -h $_HOST -p $_PORT -U $_USER -c "DROP DATABASE IF EXISTS mapasculturais_test;"
    psql -h $_HOST -p $_PORT -U $_USER -c "DROP USER IF EXISTS mapasculturais_test;"
    psql -h $_HOST -p $_PORT -U $_USER -c "CREATE USER mapasculturais_test WITH PASSWORD 'mapasculturais_test' SUPERUSER"

    createdb -h $_HOST -p $_PORT -U $_USER mapasculturais_test --owner mapasculturais_test
    psql -h $_HOST -p $_PORT -U mapasculturais_test -d mapasculturais_test < "$DIR/$FILE"
fi

if [ $_OPTION -eq 0 ] || [ $_OPTION -eq 2 ]
then
    ./compile-sass.sh localhost conf-test.php
    ./db-update.sh localhost 0 conf-test.php
    ./mc-db-updates.sh -c="conf-test.php" -p=1

    cd $DIR/src/

    echo "starting php -S on port 8888"

    MAPASCULTURAIS_CONFIG_FILE="conf-test.php" php -d variables_order=EGPCS -S 0.0.0.0:8888 &
    PID_OF_PHP=$!
    cd ..

    echo 'running tests...'
    src/protected/vendor/phpunit/phpunit/phpunit tests/

    echo "stopping php -S"
    kill $PID_OF_PHP
    cd $CDIR
fi
