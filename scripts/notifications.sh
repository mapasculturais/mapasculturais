#!/bin/bash

if [[ $1 ]]; then
    DOMAIN=$1;
else
    DOMAIN="localhost";
fi

if [[ $2 ]]; then
    SAVE_LOG=1;
else
    SAVE_LOG=0;
fi

if [ $3 ]; then
    CONFIG=$3
else
    CONFIG=config.php
fi

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR

MAPASCULTURAIS_CONFIG_FILE=$CONFIG HTTP_HOST=$DOMAIN REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' SERVER_NAME=127.0.0.1 SERVER_PORT="8000" php ../src/protected/send-notifications.php $SAVE_LOG

cd $CDIR
