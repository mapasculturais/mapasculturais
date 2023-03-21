#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR


BUILD="0"

for i in "$@"
do
case $i in
    -b|--build)
            BUILD="1"
	    shift
    ;;
    -s|--shell)
            SHELL="1"
	    shift
    ;;
    -h|--help)
    	    echo "
	run-tests.sh [-b] [-s=25]

	-b   | --build    builda a imagem Docker
	-s   | --shell    entra no container de teste
		    "
    	    exit
    ;;
esac
done

if [ $BUILD = "1" ]; then
   sudo docker-compose -f docker-compose.tests.yml build
fi

sudo docker-compose -f docker-compose.tests.yml down

if [ $SHELL = "1" ]; then
    sudo docker-compose -f docker-compose.tests.yml run --service-ports mapas_tests bash
else
    sudo docker-compose -f docker-compose.tests.yml run --service-ports mapas_tests /var/www/scripts/run-tests-docker.sh $@
fi

cd $CDIR