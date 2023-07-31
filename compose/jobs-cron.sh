#!/bin/bash
echo 'Inicializando CRON dos jobs'
while [ true ]; do
    /var/www/scripts/execute-job.sh &
    if [ -z "$JOBS_INTERVAL" ]; then 
        sleep 1
    else 
        sleep $JOBS_INTERVAL
    fi
done