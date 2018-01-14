#!/bin/bash

#docker run -it --rm -v /var/www/html:/var/www/html/ -p 9083:80 truths:nginx-php-fpm bash

docker run -td -v /var/www/html:/var/www/html/ -p 9083:80 truths:nginx-php-fpm