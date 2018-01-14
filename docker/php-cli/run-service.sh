#!/bin/bash

source /container/run-base.sh

cd /var/www/html
while true ; do sleep "$(php loader.php)" ; done
