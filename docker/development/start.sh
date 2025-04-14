#!/bin/bash

sudo -E -u www-data php -S 0.0.0.0:80 -q -t /var/www/html /var/www/dev/router.php