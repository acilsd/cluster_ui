#!/bin/bash

api_log=/var/log/truth-api-test.log
test_log=/var/log/truth-test-test.log

api_dump=true

source ../run.sh

function lower() {
	tr '[:upper:]' '[:lower:]'
}

function p_json() {
	jq '.'
}

function is_rest_okay() {
	local json="$1"
	status=$(echo "$json"| lower| jq '.status'| cut -d'"' -f2)
	if [ "$status" = okay ]; then
		return 0
	fi
	return 1
}

function rest_message() {
	local json="$1"
	echo "$json"| lower| jq '.message'
}

function rest_code() {
	local json="$1"
	echo "$json"| lower| jq '.code'
}

function test_log() {
	if [ "$1" = '-n' ]; then
		command='echo -n'
		shift;
	else
		command='echo'
	fi
	$command "$*" |tee -a $test_log
}


function api_log() {
	if [ "$1" = '-n' ]; then
		command='echo -n'
		shift;
	else
		command='echo'
	fi
	$command "$*" >> $api_log
}

function auth() {
    local login=$1
    local passwd=$2
    auth_h=''
    url="http://$cname.truths.world/one"
	api_log -n "<< GET $url  ... "
    auth=$(echo '{"login":"'$login'","passwd":"'$passwd'"}'| base64)
	res=$(wget -qO - --header "truth-auth: $auth" "$url")
	code=$?
	if [ "$code" -ne 0 ]; then
		api_log "FAIL (wget code $code)"
	else
		token=$(echo $res| lower| jq .auth.token| cut -d'"' -f2)
        if [ ! -z "$token" ] && [ "$token" != 'null' ]; then
		    api_log "OKAY"
            auth_h=$(echo '{"token":"'$token'"}'| base64)
        else
            api_log "FAIL"
        fi
	fi

	export auth_h
}

function get_raw() {
	local obj=$1
	local filter=$2
	local scope=$3
	if [ -z $scope ] ; then scope='one' ; fi
    url="http://${obj}.$cname.truths.world/$filter/$scope"
	api_log -n "<< GET $url  ... "
	res=$(wget -qO - --header "truth-auth: $auth_h" "$url")
	code=$?
	if [ "$code" -ne 0 ]; then
		api_log "FAIL (wget code $code)"
	else
		api_log "OKAY"
	fi
	echo $res
	return $code
}

function get() {
	local obj=$1
	local filter=$2
	local scope=$3
	if [ -z $scope ] ; then scope='one' ; fi
	url="http://$obj.$cname.truths.world/$filter/$scope"
	api_log -n "<< GET $url  ... "
	res=$(wget -qO - --header "truth-auth: $auth_h" "$url"| p_json)
	code=$?
	if [ "$code" -ne 0 ]; then
		api_log "FAIL (wget code $code)"
	else
		if ! is_rest_okay "$res"; then
			code=3
			api_log 'FAIL ('$(rest_code "$res")': '$(rest_message "$res")')'
		else
			api_log OKAY
		fi
	fi
	[ "$api_dump" = 'true' ] && api_log "> REQUEST:"
	[ "$api_dump" = 'true' ] && api_log "get request"
	[ "$api_dump" = 'true' ] && api_log "< RESPONSE:"
	[ "$api_dump" = 'true' ] && api_log "$res"| p_json
	echo "$res"
	return $code
}

function add() {
	local obj=$1
	local json="$2"
    url="http://$cname.truths.world/add"
	api_log -n ">> POST $url ... "
	post_data='{ "data":{'$json'}}'
	res=$(wget -qO - --header "truth-auth: $auth_h" --post-data="$post_data" "$url"| p_json)
	code=$?
	if [ "$code" -ne 0 ]; then
		api_log "FAIL (wget code $code)"
	else
		if ! is_rest_okay "$res"; then
			code=3
			api_log 'FAIL ('$(rest_message "$res")')'
		else
			api_log OKAY
		fi
	fi
	[ "$api_dump" = 'true' ] && api_log "> REQUEST:"
	[ "$api_dump" = 'true' ] && api_log "$post_data"| p_json
	[ "$api_dump" = 'true' ] && api_log "< RESPONSE:"
	[ "$api_dump" = 'true' ] && api_log "$res"| p_json
	echo "$res"
	return $code

}

function del() {
	local obj=$1
    url="http://$cname.truths.world/del"
    post_data='{ "data":["'$obj'.'$cname'.truths.world"]}' 
	api_log ">> POST $url ... "
	res=$(wget -qO - --header "truth-auth: $auth_h" --post-data="$post_data" "$url"| p_json)
	code=$?
	if [ "$code" -ne 0 ]; then
		api_log "FAIL (wget code $code)"
	else
		if ! is_rest_okay "$res"; then
			code=3
			api_log 'FAIL ('$(rest_message "$res")')'
		else
			api_log OKAY
		fi
	fi
	[ "$api_dump" = 'true' ] && api_log "> REQUEST:"
	[ "$api_dump" = 'true' ] && api_log "$post_data"| p_json
	[ "$api_dump" = 'true' ] && api_log "< RESPONSE:"
	[ "$api_dump" = 'true' ] && api_log "$res"| p_json
	echo "$res"
	return $code
}

function mod() {
	local obj=$1
	local json="$2"
    url="http://$cname.truths.world/mod"
	api_log -n ">> POST $url ... "
    post_data='{ "data":{"'$obj'.'$cname'.truths.world":{'$json'}}}' 
	res=$(wget -qO - --header "truth-auth: $auth_h" --post-data="$post_data" "$url"| p_json)
	code=$?
	if [ "$code" -ne 0 ]; then
		api_log "FAIL (wget code $code)"
	else
		if ! is_rest_okay "$res"; then
			code=3
			api_log 'FAIL ('$(rest_message "$res")')'
		else
			api_log OKAY
		fi
	fi
	[ "$api_dump" = 'true' ] && api_log "> REQUEST:"
	[ "$api_dump" = 'true' ] && api_log "$post_data"| p_json
	[ "$api_dump" = 'true' ] && api_log "< RESPONSE:"
	[ "$api_dump" = 'true' ] && api_log "$res"| p_json
	echo "$res"
	return $code

}

expect_data() {
	local data="$1"
	shift
	local cmd="$*"

	test_log -n "> $cmd"
	res="$($cmd)"
	code=$?
	test_log -n " ... "
	if [ "$code" -ne 0 ]; then
		test_log FAIL
	else
		if [ "$res" = "$data" ]; then
			test_log OKAY
		else
			test_log FAIL
		fi
	fi
	return $code
}


expect_test() {
	local field="$1"
	local oper="$2"
	local val="$3"
	shift 3
	local cmd="$*"

	field=$(echo "$field"| lower)
	test_log -n "> $cmd"
	res="$($cmd)"
	code=$?
	test_log -n " ... "
	if [ "$code" -ne 0 ]; then
		test_log FAIL
	else
		if [ "$(echo $res| lower| jq .$field| cut -d'"' -f2)" "$oper" "$val" ]; then
			test_log OKAY
		else
			test_log FAIL
		fi
	fi
	return $code
}

expect_fail() {
	local cmd="$*"
	test_log -n "> $cmd"
	res="$($cmd)"
	code=$?
	test_log -n " ... "
	if [ "$code" -eq 0 ]; then
		test_log FAIL
	else
		test_log OKAY
	fi
	return $code
}

expect_okay() {
	local cmd="$*"
	test_log -n "> $cmd"
	res="$($cmd)"
	code=$?
	test_log -n " ... "
	if [ "$code" -ne 0 ]; then
		test_log FAIL
	else
		test_log OKAY
	fi
	return $code
}

function block() {
	local msg="$1"
	echo ',_________'
	echo '|'$msg
}


# remove all objects of a certain objectclass.
# may fail at recurcive deletion.
function purge() {
	local type="$1"
	ldapsearch -H ldap://$ip -b dc=trixie -D cn=admin,dc=trixie -w truth-f9649b2b905b849f502382ce87033bcd "(objectClass=trueType$type)" ''|\
	       grep 'dn:'| cut -d':' -f2-|\
	       ldapdelete -H ldap://$ip  -D cn=admin,dc=trixie -w truth-f9649b2b905b849f502382ce87033bcd >>/dev/null
}
