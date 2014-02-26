#!/bin/bash

sudo -u postgres psql -d mapasculturais -f "$1"
