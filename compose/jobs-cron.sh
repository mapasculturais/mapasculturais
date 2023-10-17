#!/bin/bash
BASHPID=$$
NUM_CORES=$(grep -c ^processor /proc/cpuinfo)

if [ -z "$NUM_PROCESSES" ]; then
    NUM_PROCESSES=$NUM_CORES
fi

while [ true ]; do
    CHILDREN=`ps -eo ppid | grep -w $BASHPID`
    NUM_CHILDREN=`echo $CHILDREN | wc -w`
    
    if [ $NUM_PROCESSES -ge $NUM_CHILDREN ]; then
        /var/www/scripts/execute-job.sh &
    fi
    
    if [ -z "$JOBS_INTERVAL" ]; then 
        sleep 1
    else 
        sleep $JOBS_INTERVAL
    fi
done