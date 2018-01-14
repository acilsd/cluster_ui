#!/bin/bash

. lib.sh

# GET
function test_add_field() {
	local name=$1
	local type=$2
	rest_post add field-post '{"name":"'BashTests_$name',"type":"'$type'"}'
}

function test_del_field() {
	local name=$1
	rest_post del field-post '{"'$name'"}'
}

function run_tests() {
	# create a bunch of fields
	run okay test_add_field A int
	run okay test_add_field B int
	run okay test_add_field C string
	run okay test_add_field D string
	run okay test_add_field E bool

	# fail to create fields with same names
	# of same and different types
	run fail test_add_field A int
	run fail test_add_field A string
	run fail test_add_field D string
	run fail test_add_field F untype
	run fail test_add_field E string

	# remove all created stuff
	for i in {A..F}; do
		run okay test_del_field $i
	done

	# fail to remove unexistent stuff
	run fail test_del_field A
	run fail test_del_field D
	run fail test_del_field UNFIELD
}
