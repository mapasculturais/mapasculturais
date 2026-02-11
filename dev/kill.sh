#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CDIR=$( pwd )
cd "$DIR/.."

docker compose kill mapas

cd "$CDIR"
