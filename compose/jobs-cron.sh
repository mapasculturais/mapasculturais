#!/bin/bash
while [ true ]; do
    time /var/www/scripts/execute-job.sh &
    if [ -z "$JOBS_INTERVAL" ]; then 
        sleep 1
    else 
        sleep $JOBS_INTERVAL
    fi
done