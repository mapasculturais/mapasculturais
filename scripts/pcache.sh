#!/bin/bash

if [[ $1 ]]; then
    SERVER_NAME=$1;
else
    SERVER_NAME="127.0.0.1";
fi

if [[ $2 ]]; then
    URL=$2;
else
    URL="permissionCache/recreate";
fi

if [[ $3 ]]; then
    SERVER_PORT=$3;
else
    SERVER_PORT="80";
fi


if [[ $4 ]]; then
    SERVER_PROTOCOL=$4;
else
    SERVER_PROTOCOL="http";
fi

wget $SERVER_PROTOCOL://$SERVER_NAME:$SERVER_PORT/$URL --delete-after
