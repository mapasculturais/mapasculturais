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
    -u|--update)
            BUILD="1"
	    rm src/protected/composer.lock
	    shift
    ;;
    -h|--help)
        echo "
        start-dev.sh

        -b, --build     Realiza build
        -u, --update    Atualiza composer e builda
        -h, --help      Ajuda
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