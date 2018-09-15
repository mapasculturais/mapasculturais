#!/bin/bash

sudo docker exec -it $(docker-compose -f docker-compose.local.yml ps -q mapas) sh /var/www/scripts/shell.sh
