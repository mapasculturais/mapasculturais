#!/bin/bash
if [[ $1 ]]; then
	BRANCH=$1
else
	BRANCH="stable"
fi

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $DIR/..

git pull --all

git checkout $BRANCH

cd $DIR/../src/protected/

composer.phar update

composer.phar dump-autoload --optimize

cd tools

./doctrine orm:generate-proxies

