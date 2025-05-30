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
    -h|--help)
    	    echo "
	bash.sh [-b] [-u] [-d] [-s=25]

    -b=  | --build      builda a imagem Docker
    -h=  | --help       Imprime este texto de ajuda
		    "
    	    exit
    ;;
esac
done

if [ $BUILD = "1" ]; then
   docker compose build
fi

docker compose down --remove-orphans
docker compose run --service-ports mapas /var/www/vendor/bin/phpunit /var/www/tests --colors=always $@
cd $CDIR
