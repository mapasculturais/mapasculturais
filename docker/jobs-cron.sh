#!/bin/bash
echo 'Inicializando CRON dos jobs'

NUM_CORES=$(grep -c ^processor /proc/cpuinfo)

if [ -z "$NUM_PROCESSES" ]; then
    NUM_PROCESSES=$NUM_CORES
fi

bash_pid=$$

while [ true ]; do
    children=`ps -eo ppid | grep -w $bash_pid`
    NUM_CHILDREN=`echo $children | wc -w`
    
    if [ $NUM_PROCESSES -ge $NUM_CHILDREN ]; then
        /var/www/scripts/execute-job.sh &
    fi
    
    if [ -z "$JOBS_INTERVAL" ]; then 
        sleep 1
    else 
        sleep $JOBS_INTERVAL
    fi
done