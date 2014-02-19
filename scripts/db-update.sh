#!/bin/bash

sudo -u postgres psql -d mapasculturais < $1
