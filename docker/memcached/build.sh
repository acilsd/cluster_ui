#!/bin/bash
source ../lib.sh

docker pull memcached
docker tag memcached truths.world:5555/memcached:$(git_hash)
push memcached
