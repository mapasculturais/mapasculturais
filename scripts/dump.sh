#!/bin/bash
mv ../db/dump.sql ../db/dump.bkp.sql
sudo -u postgres pg_dump --create mapasculturais > ../db/dump.sql
