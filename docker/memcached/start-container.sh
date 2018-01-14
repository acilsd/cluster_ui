#!/bin/bash

#docker run -it --rm -v /var/www/html:/var/www/html/ -p 11211:11211 -e MEM_SIZE=55 truths:memcached bash
docker run --rm -d -p 11211:11211 -e MEM_SIZE=55 truths:memcached
