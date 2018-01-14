#!/bin/bash

function log() {
	echo "$*" > /tmp/truth-tests.log
}

function run() {
	local name=$1
	local name=$res
	shift

	echo -n "$name ... "
	out="$(test_$name $*)"
	stat=$?

	# happy path
	if [ "$stat" -eq 0 ] && [ "$res" = 'okay' ]; then
		echo OK
		return 0
	# fail path
	else if [ "$res" = 'fail' ]; then
		# expected fail
		echo OK
		return 0
	else
		# unexpected fail
		echo FAIL
		log "failed - $name"
		log "$out"
		return 1
	fi
}

function getservicebyname() {
	local name=$1
	getent services| grep truth-"$name"| awk '{ print $2 }'| cut -d'/' -f1
}

function getservicebyport() {
	local port=$1
	getent services| grep truth-"$name"| awk '{ print $1 }'|
}

function wget_post() {
	local url="$1"
	local data="$2"
	wget -qO - --post-data="$data"
}

function rest_post() {
	local scope=$1
	local svc=$2
	local url="http://127.0.0.1:$(getservicebyname $svc)/$scope"
	local data="$2"
	wget_post "$url" '{ "data":'"$data"'}'
}
