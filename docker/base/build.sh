#!/bin/bash
source ../lib.sh
cont_name="$(basename $(pwd))"

build $cont_name
push $cont_name

cur_base=$(sudo docker images |grep $cont_name |grep $(git_hash) |awk '{print $3}')
docker tag $cur_base "$registry/$cont_name:latest"
