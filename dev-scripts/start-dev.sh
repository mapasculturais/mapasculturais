#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR


BUILD="0"
SLEEP_TIME="0"

if [ ! -d "../docker-data/postgres" ]; then
  SLEEP_TIME=15
fi

for i in "$@"
do
case $i in
    -b|--build)
            BUILD="1"
	    shift
    ;;

    -s|--sleep)
            SLEEP_TIME="${i#*=}"
	    shift
    ;;
    -u|--update)
            BUILD="1"
	    rm ../src/protected/composer.lock
	    shift
    ;;
    -h|--help)
    	    echo "
	run-tests.sh [-b] [-u] [-s=25]

    -b=  | --build      builda a imagem Docker
    -u=  | --update     atualiza os pacotes do composer
    -s=  | --sleep=     tempo de espera em segundos para o banco de dados ser inicializado (padrão: 0 se existir a pasta docker-data/postgres ou 15 se não existir)
		    "
    	    exit
    ;;
esac
done

if [ $BUILD = "1" ]; then
   sudo docker-compose -f docker-compose.local.yml build
fi

sudo docker-compose -f docker-compose.local.yml run --service-ports  mapas

cd $CDIR