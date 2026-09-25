#!/bin/bash
echo 'Inicializando CRON de limpeza de assets orfaos'

bash_pid=$$
renice +19 -p "$bash_pid" >/dev/null 2>&1
ionice -c 3 -p "$bash_pid" >/dev/null 2>&1

while [ true ]; do
    /var/www/scripts/cleanup-orphan-assets.sh 2>/dev/null

    if [ -z "$ASSET_CLEANUP_INTERVAL" ]; then
        sleep 21600
    else
        sleep $ASSET_CLEANUP_INTERVAL
    fi
done
