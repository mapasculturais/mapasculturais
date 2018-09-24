#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR


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
    -h|--help)
    	    echo "
	run-tests.sh [-b] [-d]

	-b=  | --build   builda a imagem Docker
	-d=  | --down    executa o docker-compose down antes do docker-compose run
		    "
    	    exit
    ;;
esac
done

if [ $BUILD = "1" ]; then
   sudo docker-compose -f docker-compose.local.yml build
fi

if [ $DOWN = "1" ]; then
   sudo docker-compose -f docker-compose.local.yml down
fi

sudo rm -rf ../docker-data/pcache-cron.log
sudo touch ../docker-data/pcache-cron.log

sudo docker-compose -f docker-compose.local.yml run --service-ports  mapas

cd $CDIR
