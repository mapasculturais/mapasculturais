#!/bin/bash
while [ true ]; do
    /var/www/scripts/recreate-pending-pcache.sh &
    if [ -z "$PENDING_PCACHE_RECREATION_INTERVAL" ]; then 
        sleep 1
    else 
        sleep $PENDING_PCACHE_RECREATION_INTERVAL
    fi
done