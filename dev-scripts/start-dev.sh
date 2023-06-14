#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR

MAPAS_NAME=mapas-run

BUILD="0"
DOWN="0"

for i in "$@"
do
case $i in
    -b|--build)
            BUILD="1"
	    shift
    ;;
    -d|--down)
            DOWN="1"
	    shift
    ;;
    -u|--update)
            BUILD="1"
	    rm ../src/protected/composer.lock
	    shift
    ;;
    -h|--help)
    	    echo "
	run-tests.sh [-b] [-u] [-d] [-s=25]

    -b=  | --build      builda a imagem Docker
    -u=  | --update     atualiza os pacotes do composer
	-d=  | --down       executa o docker-compose down antes do docker-compose run
    -h=  | --help       Imprime este texto de ajuda
		    "
    	    exit
    ;;
esac
done

if [ $BUILD = "1" ]; then
   docker-compose build
fi

if [ $DOWN = "1" ]; then
   docker-compose down
fi

docker-compose run --name=$MAPAS_NAME --service-ports mapas 

docker-compose down --remove-orphans
cd $CDIR
