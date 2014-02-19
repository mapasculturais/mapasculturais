#!/bin/bash
sudo -u postgres dropdb mapasculturais
sudo -u postgres psql < ../db/dump.sql
