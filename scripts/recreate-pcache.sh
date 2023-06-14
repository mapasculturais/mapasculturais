#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )

cd $DIR

./mc-db-updates.sh -n="recreate pcache"

cd $CDIR
