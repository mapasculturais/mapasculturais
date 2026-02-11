#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd "$DIR/.."

BUILD="0"
DOWN="0"

for i in "$@"
do
case $i in
    -b|--build)
            BUILD="1"
            shift
    ;;
    -d|--down)
            DOWN="1"
            shift
    ;;
    -h|--help)
            echo "
    start.sh [-b] [-d]

    -b | --build    rebuild the Docker image before starting
    -d | --down     bring containers down before starting
    -h | --help     print this help text
            "
            exit
    ;;
esac
done

if [ $DOWN = "1" ]; then
    docker compose down --remove-orphans
fi

if [ $BUILD = "1" ]; then
    docker compose build
fi

docker compose up

cd "$CDIR"
