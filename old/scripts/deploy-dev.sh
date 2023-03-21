#!/bin/bash

DOMAIN="localhost"

for i in "$@"
do
case $i in
    -d=*|--domain=*)
	    DOMAIN="${i#*=}"
	    shift # past argument=value
    ;;
    *)
        DOMAIN=$i
    ;;
esac
done

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd $DIR

./deploy.sh --domain=$DOMAIN --mode=development

cd $CDIR