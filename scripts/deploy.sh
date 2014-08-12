#!/bin/bash
if [[ $1 ]]; then
	BRANCH=$1
else
	BRANCH="stable"
fi

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $DIR

./db-update.sh 1

cd $DIR/..

git pull --all

git checkout $BRANCH

cd $DIR/../src/protected/
if hash composer.phar 2>/dev/null; then
	composer="composer.phar"
else
	composer="composer"
fi

$composer update

$composer dump-autoload --optimize

cd tools

REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' SERVER_NAME=127.0.0.1 SERVER_PORT="8000" ./doctrine orm:generate-proxies

