#!/bin/bash
if [ $1 ]; then
	DOMAIN=$1
else
	DOMAIN=localhost
fi

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR

cd $DIR/..

git pull --all

cd $DIR/../src/protected/

if hash composer.phar 2>/dev/null; then
	composer="composer.phar"
else
	composer="composer"
fi

$composer install --prefer-dist

$composer dump-autoload --optimize

cd tools

HTTP_HOST=$DOMAIN REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' SERVER_NAME=127.0.0.1 SERVER_PORT="8000" ./doctrine orm:generate-proxies


cd $DIR
./db-update.sh $DOMAIN
./compile-sass.sh $DOMAIN

cd $CDIR
