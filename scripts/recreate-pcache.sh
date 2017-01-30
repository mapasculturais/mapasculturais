#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )

cd $DIR/..

NUM_CORES=$(grep -c ^processor /proc/cpuinfo)
if [ $NUM_CORES -gt 1 ]; then
	NUM_PROCESSES=$(($NUM_CORES + 3))
else
	NUM_PROCESSES=1
fi

if [[ $1 ]]; then
    NUM_PROCESSES=$1
fi

HTTP_HOST=$DOMAIN REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' SERVER_NAME=127.0.0.1 SERVER_PORT="8000" php src/protected/tools/recreate-pcache.php delete

COUNTER=0
while [  $COUNTER -lt $NUM_PROCESSES ]; do
    let COUNTER=COUNTER+1 

    HTTP_HOST=$DOMAIN REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' SERVER_NAME=127.0.0.1 SERVER_PORT="8000" php src/protected/tools/recreate-pcache.php $NUM_PROCESSES $COUNTER& 
done
