#!/bin/bash

source /container/run-base.sh

/etc/init.d/php7.0-fpm start

/usr/sbin/nginx -g "daemon off; error_log /dev/stderr info;"
