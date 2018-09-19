#!/bin/bash

while [ true ]; do
    /var/www/scripts/recreate-pending-pcache.sh
    if [ -z "$PENDING_PCACHE_RECREATION_INTERVAL" ]; then 
        sleep $PENDING_PCACHE_RECREATION_INTERVAL; 
    else 
        sleep 60; 
    fi
done