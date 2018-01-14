#!/bin/bash

#git log --pretty=format:'%h' -n 1
#docker build . --tag truths/php-cli:
docker run -td -v /var/www/html:/var/www/html/ truths:php-cli