#!/bin/bash

#source ../run.sh

registry=truths.world:5555

function git_hash() {
	t="$(git log --pretty=format:'%h' -n 1)"
	echo $t
}

function build() {
    local cont_name="$1"

    git_hash=$(git_hash)
    docker build . --tag "truths.world:5555/$cont_name:$git_hash"
}

function push() {
    local cont_name="$1"

    git_hash=$(git_hash)
    docker push "$registry/$cont_name:$git_hash"
    if [ "$cont_name" = 'base' ]; then
        docker push "$registry/$cont_name:latest"
    fi
}
