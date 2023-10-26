#!/bin/bash
#set -e                      #Exit immediately if a command exits with a non-zero status.
set -o pipefail

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}")"/../ && pwd )"
CDIR=$(pwd)

if [ -f /tmp/mapasculturais-tests-authenticated-user.id ]
then
    rm "/tmp/mapasculturais-tests-authenticated-user.id"
fi

cd $DIR/src/

echo "starting php -S on port 8888"
php -d variables_order=EGPCS -S 0.0.0.0:8888 &
PID_OF_PHP=$!
cd ..
echo 'running tests...'
if [ $1 ]; then
    vendor/phpunit/phpunit/phpunit tests/$1
else
    vendor/phpunit/phpunit/phpunit tests/
fi

echo "stopping php -S"
kill $PID_OF_PHP
cd $CDIR