#!/bin/bash
# Reduz prioridade de CPU e I/O de todos os processos pcache em execução.
for pid in $(pgrep -f recreate-pending-pcache.php 2>/dev/null); do
    ni=$(ps -o ni= -p "$pid" 2>/dev/null | tr -d ' ')
    if [ "$ni" != "19" ]; then
        renice +19 -p "$pid" >/dev/null 2>&1
    fi
    ionice -c 3 -p "$pid" >/dev/null 2>&1
done
