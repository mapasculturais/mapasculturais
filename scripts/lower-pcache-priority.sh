#!/bin/bash
# Reduz prioridade de CPU e I/O de todos os processos pcache em execução.
for pid in $(pgrep -f recreate-pending-pcache.php 2>/dev/null); do
    renice +19 -p "$pid" 2>/dev/null
    ionice -c 3 -p "$pid" 2>/dev/null
done
