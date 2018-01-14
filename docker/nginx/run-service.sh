#!/bin/bash

source /container/run-base.sh

get_port=$(getent services| grep 'truths-rest-get'| cut -d'/' -f1| awk '{ print $2 }')
post_port=$(getent services| grep 'truths-rest-post'| cut -d'/' -f1| awk '{ print $2 }')
echo "running nginx for $(get_cname) get: $get_port, post: $post_port"

sed -i "s/SERVER_NAME/$(get_cname)/g" /etc/nginx/sites-enabled/server
sed -i "s/GET_PORT/$get_port/g" /etc/nginx/sites-enabled/server
sed -i "s/POST_PORT/$post_port/g" /etc/nginx/sites-enabled/server
sed -i "s/PROXY_IP/$(getvar ip)/g" /etc/nginx/sites-enabled/server

/usr/sbin/nginx -g "daemon off; error_log /dev/stderr info;"
