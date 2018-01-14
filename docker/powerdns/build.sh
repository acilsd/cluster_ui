#!/bin/bash
source ../lib.sh
cont_name="$(basename $(pwd))"

build $cont_name
push $cont_name
