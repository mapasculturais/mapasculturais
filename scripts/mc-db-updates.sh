#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )

cd $DIR/..

NUM_CORES=$(grep -c ^processor /proc/cpuinfo)
if [ -z ${MC_UPDATES_PROCESSES} ]; then 
    if [ $NUM_CORES -gt 1 ]; then
        NUM_PROCESSES=$(($NUM_CORES))
    else
        NUM_PROCESSES=1
    fi
else
    NUM_PROCESSES=$MC_UPDATES_PROCESSES
fi

CONFIG='config.php';


NAME=""

for i in "$@"
do
case $i in

    -p=*|--processes=*)
        NUM_PROCESSES="${i#*=}"
        shift # past argument=value
    ;;
    -c=*|--config=*)
        CONFIG="${i#*=}"
        shift # past argument=value
    ;;
    -n=*|--name=*)
        NAME="${i#*=}"
        shift # past argument=value
    ;;    
    -d=*|--domain=*)
        DOMAIN="${i#*=}"
        shift # past argument=value
    ;;

    -h|--help)
    	    echo "
	mc-db-updates.sh [-p=8] [-n='recreate pcache'] [-d=dominio.da.app.saas.gov.br]

	-p=  | --processes=  numero de processos (padrão é o número de cores)
	-n=  | --name=       o nome do db-update que deve rodar
"
    	    exit
    ;;
esac
done

echo "INICIANDO $NUM_PROCESSES PROCESSOS...";
COUNTER=0
while [  $COUNTER -lt $NUM_PROCESSES ]; do
    let COUNTER=COUNTER+1 
    if [ $NUM_PROCESSES -eq 1 ]; then
        MAPASCULTURAIS_CONFIG_FILE=$CONFIG HTTP_HOST=$DOMAIN REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' SERVER_NAME=127.0.0.1 SERVER_PORT="8000" php src/tools/apply-multicore-db-update.php $NUM_PROCESSES $COUNTER "$NAME"
    else
        MAPASCULTURAIS_CONFIG_FILE=$CONFIG HTTP_HOST=$DOMAIN REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' SERVER_NAME=127.0.0.1 SERVER_PORT="8000" php src/tools/apply-multicore-db-update.php $NUM_PROCESSES $COUNTER "$NAME"&
    fi
done
