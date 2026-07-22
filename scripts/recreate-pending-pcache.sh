#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )

cd $DIR

"$DIR/lower-pcache-priority.sh"

LOG_HOOK=true REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' SERVER_NAME=127.0.0.1 SERVER_PORT="8000" nice -n 19 ionice -c 3 php ../src/tools/recreate-pending-pcache.php

cd $CDIR
