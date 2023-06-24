#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR

if [[ $1 ]]; then
    DOMAIN=$1;
else
    DOMAIN="localhost";
fi

if [[ $2 ]]; then
    SAVE_LOG=$2;
else
    SAVE_LOG=0;
fi

if [ $3 ]; then
    CONFIG=$3
else
    CONFIG=config.php
fi

## primeiro executa sem a inclusão dos plugins, para aplicar as modificações do core
DISABLE_PLUGINS=1 MAPASCULTURAIS_CONFIG_FILE=$CONFIG HTTP_HOST=$DOMAIN REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' SERVER_NAME=127.0.0.1 SERVER_PORT="8000" php ../src/tools/apply-updates.php $SAVE_LOG

## depois executa incluindo os plugins
MAPASCULTURAIS_CONFIG_FILE=$CONFIG HTTP_HOST=$DOMAIN REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' SERVER_NAME=127.0.0.1 SERVER_PORT="8000" php ../src/tools/apply-updates.php $SAVE_LOG

cd $CDIR
