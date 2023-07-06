#!/bin/bash

DOMAIN="localhost"
MODE="production"

for i in "$@"
do
case $i in
    -d=*|--domain=*)
	    DOMAIN="${i#*=}"
	    shift # past argument=value
    ;;
    -m=*|--mode=*)
	    MODE="${i#*=}"
	    shift # past argument=value
    ;;
    --dev|--devel|--development)
    	    MODE="development"
            shift # past argument with no value
    ;;
    *)
            DOMAIN=$i
    ;;
esac
done


DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR

cd $DIR/..

#git pull --all

cd $DIR/..

if hash composer.phar 2>/dev/null; then
	composer="composer.phar"
else
	composer="composer"
fi

if [[ $MODE == 'development' ]]; then
	$composer install --prefer-dist 
else
	$composer install --prefer-dist --no-dev
fi;


$composer dump-autoload --optimize

cd src/tools

HTTP_HOST=$DOMAIN REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' SERVER_NAME=127.0.0.1 SERVER_PORT="8000" ./doctrine orm:generate-proxies


cd $DIR
./compile-sass.sh
./db-update.sh $DOMAIN
./mc-db-updates.sh

cd $CDIR
