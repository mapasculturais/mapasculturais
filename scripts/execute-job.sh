#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )

cd $DIR

REQUEST_METHOD='CLI' REMOTE_ADDR='127.0.0.1' REQUEST_URI='/' HTTP_HOST=localhost SERVER_NAME=127.0.0.1 SERVER_PORT="8000" php ../src/protected/tools/execute-job.php

cd $CDIR