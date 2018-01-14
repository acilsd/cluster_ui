#!/bin/bash
source ../../lib.sh
git_hash=$(git_hash)
cont_name="dummy"
docker build . --tag "truths.world:5555/$cont_name:$git_hash"
docker push "truths.world:5555/$cont_name"
