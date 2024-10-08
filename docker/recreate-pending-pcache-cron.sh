#!/bin/bash
echo 'Inicializando CRON do pcache'

NUM_CORES=$(grep -c ^processor /proc/cpuinfo)

if [ -z "$NUM_PROCESSES" ]; then
    NUM_PROCESSES=$NUM_CORES
fi

bash_pid=$$

while [ true ]; do
    children=`ps -eo ppid | grep -w $bash_pid`
    NUM_CHILDREN=`echo $children | wc -w`
    
    if [ $NUM_PROCESSES -ge $NUM_CHILDREN ]; then
        /app/scripts/recreate-pending-pcache.sh &
    fi
    
    if [ -z "$PENDING_PCACHE_RECREATION_INTERVAL" ]; then 
        sleep 1
    else 
        sleep $PENDING_PCACHE_RECREATION_INTERVAL
    fi
done