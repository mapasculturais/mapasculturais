#!/bin/bash

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

$composer update --prefer-dist

$composer dump-autoload --optimize

cd tools

REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' SERVER_NAME=127.0.0.1 SERVER_PORT="8000" ./doctrine orm:generate-proxies

cd $DIR
./db-update.sh 1

cd $CDIR

restart php5-fpm
