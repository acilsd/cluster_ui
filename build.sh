#!/bin/bash

function build_container() {
    cont="$1"
    pushd "$cont"
    bash ./build.sh
    popd
}

function build_containers() {
    dir="$1"
    pushd "$dir"
    for cont in *; do
        if [ -d "$cont" ]; then
            if [ -f "$cont/build.sh" ]; then
                echo
                echo
                echo building $cont
                build_container "$cont"
            fi
        fi
    done
    popd
}

docker ps| grep -q registry || \
    docker run --rm --name registry --detach -p 5555:5000 registry:2

build_containers docker
