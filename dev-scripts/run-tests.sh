#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR


BUILD="0"
SLEEP_TIME="10"

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
    -h|--help)
    	    echo "
	run-tests.sh [-b] [-s=25]

	-b=  | --build    builda a imagem Docker
	-s=  | --sleep=   tempo de espera para o banco de dados ser inicializado (padrão: 10 segundos)
		    "
    	    exit
    ;;
esac
done

if [ $BUILD = "1" ]; then
   sudo docker-compose -f docker-compose.tests.yml build
fi

sudo docker-compose -f docker-compose.tests.yml down
sudo docker-compose -f docker-compose.tests.yml run mapas echo "ready" > /dev/null
echo "aguardando $SLEEP_TIME segundos para que a base de dados seja inicializada"
sleep $SLEEP_TIME
sudo docker-compose -f docker-compose.tests.yml run --service-ports  mapas

cd $CDIR