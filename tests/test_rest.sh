#!/bin/bash

source lib.sh

# we have two types of objects here: city and street.
# we are going to create a hierarchy of objects with
# two cities, having multiple streets each.

# city
function get_city() {
	local name=$1
	get "$name"
}

function add_city() {
	local name=$1
    add truths.world '"'$name'.'$cname'.truths.world" : {"type" : "testPonyCity", "testCityName" : "'$name'", "testCityRuler" : "Celestia" }'
}

function del_city() {
	local name=$1
	del "$name"
}
# /city

# street
function get_street() {
	local city=$1
	local street=$2
	get "$street.$city"
}

function add_street() {
	local city=$1
	local street=$2
    add truths.world '"'$street'.'$city'.'$cname'.truths.world" : {"type" : "testPonyStreet", "testStreetName" : "'$street'" }'
}

function del_street() {
	local city=$1
	local street=$2
	del "$street.$city"
}
# /street

# house 
function get_house() {
	local city=$1
	local street=$2
	local house=$3
	get "$house.$street.$city"
}

function get_house_colour() {
	local city=$1
	local street=$2
	local house=$3
	get "$house.$street.$city" ":testHouseColour"
}

function get_house_colour_raw() {
	local city=$1
	local street=$2
	local house=$3
	get_raw "$house.$street.$city" "::testHouseColour"
}

function add_house() {
	local city=$1
	local street=$2
	local house=$3
	local pony=$4
    add truths.world '"'$house.$street.$city'.'$cname'.truths.world" : {"type" : "testPonyHouse", "testPonyName" : "'$pony'", "testHouseColour": "green" }'
}

function del_house() {
	local city=$1
	local street=$2
	local house=$3
	del "$house.$street.$city"
}

function mod_house() {
	local city=$1
	local street=$2
	local house=$3
	local colour=$4
	mod "$house.$street.$city" '"testHouseColour":"'$colour'"'
}
# /house


function cleanup() {
	echo -n "purging objects by types: "
	for oc in testPonyHouse testPonyStreet testPonyCity ; do
		echo -n " $oc"
		purge "$oc"
	done
	echo "; done"
}

function add_cities() {
	while true; do
		city="$1"
		shift
		if [ "$city" = "" ]; then
			break;
		fi
		expect_okay add_city "$city"
	done
}

function add_streets() {
	city="$1"
	shift
	while true; do
		street="$1"
		shift
		if [ "$street" = "" ]; then
			break;
		fi
		expect_okay add_street $city $street
	done
	
}

function add_houses() {
	city="$1"
	street="$2"
	shift 2
	while true; do
		house="$1"
		pony="$2"
		shift 2
		if [ "$pony" = "" ]; then
			break;
		fi
		expect_okay add_house $city $street $house $pony
	done
}

function add_flower() {
	local city=$1
	local street=$2
	local colour=$3
	local name=$4
    add truths.world '"'$name.$street.$city'.'$cname'.truths.world" : {"type" : "testFlower", "testFlowerColour" : "'$colour'", "testFlowerName": "'$name'" }'
}

function add_flowers() {
	city="$1"
	street="$2"
	shift 2
	while true; do
		colour="$1"
		name="$2"
		shift 2
		if [ "$name" = "" ]; then
			break;
		fi
		expect_okay add_flower $city $street $colour $name
	done
}

function set_up() {

	block 'Adding cities'
	add_cities Kanterlot Ponyville

	block 'Adding streets'
	add_streets Ponyville FlowerGarden LibraryStr 154Ave_SW TrixieStr
	add_streets Kanterlot Main 1st 2nd 3rd 4th 5th

	block 'Adding houses'
	add_houses Ponyville LibraryStr Library TwilightSparkle \
		Carousel Rarity Cloud RainbowDash
	add_houses Ponyville 154Ave_SW SweetAppleAcres AppleJack TreeHouse FlutterShy
	add_houses Ponyville FlowerGarden School Cheerilee
	add_houses Kanterlot Main Castle Celestia Tower Luna

	block 'Adding flowers'
  add_flowers Ponyville FlowerGarden green chamomile red rose purple violet rainbow awesome
}

function repaint_houses() {

	block 'Modifying houses colours'
	expect_okay mod_house Ponyville 154Ave_SW SweetAppleAcres red
	expect_okay mod_house Ponyville LibraryStr Cloud rainbow
	expect_okay mod_house Ponyville LibraryStr Carousel purple
}

function test_del() {

	block 'Tearing down'
	block 'Erasing data non recursive'
	expect_okay del_house Ponyville LibraryStr Carousel 
	expect_okay del_house Ponyville LibraryStr Library 

	block 'Testing expected get failures'
	expect_fail get_house Ponyville LibraryStr Carousel 
	expect_fail get_house Ponyville LibraryStr Library

	block 'Erasing data recursive'
	expect_okay del_street Ponyville 154Ave_SW
	expect_okay del_street Ponyville TrixieStr

	block 'Testing expected get failures'
	expect_fail get_house Ponyville 154Ave_SW Treehouse
	expect_fail get_house Ponyville 154Ave_SW SweetAppleAcres 
	expect_fail get_street Ponyville 154Ave_SW
	expect_fail get_street Ponyville TrixieStr
}

function tear_down() {

	expect_okay del_city Ponyville
	expect_okay del_city Kanterlot
	expect_fail get_city Ponyville
	expect_fail get_city Kanterlot
}

function test_single_gets() {

	block 'Various gets'
	expect_okay get_city Ponyville
	expect_okay get_street Ponyville LibraryStr
	expect_okay get_street Ponyville 154Ave_SW

	expect_okay get_house Ponyville LibraryStr Carousel 
	expect_okay get_house Ponyville 154Ave_SW Treehouse
	expect_okay get_house Ponyville 154Ave_SW SweetAppleAcres 

	block 'Testing values'
	expect_test data.testHouseColour = purple get_house Ponyville LibraryStr Carousel
	expect_test data.testHouseColour = green get_house Ponyville 154Ave_SW Treehouse
	expect_test data.testHouseColour = rainbow get_house Ponyville LibraryStr Cloud
	expect_test data.testHouseColour = red get_house Ponyville 154Ave_SW SweetAppleAcres 
}

function test_fields_filter_get() {

	block 'Various gets with field filter set'
	expect_test data.testHouseColour = purple get_house_colour Ponyville LibraryStr Carousel 
	expect_test data.testHouseColour = purple get_house_colour Ponyville LibraryStr Carousel
	expect_test data.testHouseColour = green get_house_colour Ponyville 154Ave_SW Treehouse
	expect_test data.testHouseColour = rainbow get_house_colour Ponyville LibraryStr Cloud
}

function test_raw_fields() {

	block 'Raw fields'
	expect_data purple get_house_colour_raw Ponyville LibraryStr Carousel 
	expect_data purple get_house_colour_raw Ponyville LibraryStr Carousel
	expect_data green get_house_colour_raw Ponyville 154Ave_SW Treehouse
	expect_data rainbow get_house_colour_raw Ponyville LibraryStr Cloud
}

function test_fields_get() {

    test_fields_filter_get
    #test_raw_fields
}

#TODO :where, :union and :join filters test, ex:
# wget -qO - --header "truth-auth: $auth_h" http://Ponyville.rei.truths.world/tree/:where=$(echo -n 'type=testPonyHouse'| base64)/:join=$(echo -n 'http://FlowerGarden.Ponyville.rei.truths.world/list,testhousecolour=testflowercolour'| base64 -w 0)/:testponyname/:testflowername

function sanity() {

	#cleanup
    auth testuser testpassword
    if [[ -z $auth_h ]] ; then
        echo "auth failed"
    else
        echo "auth header $auth_h"
    fi

	set_up


	repaint_houses
	test_single_gets
	test_fields_get
	
    test_del
	tear_down
	#cleanup

}

function insanity() {
    auth testuser testpassword
    if [[ -z $auth_h ]] ; then
        echo "auth failed"
    else
        echo "auth header $auth_h"
    fi

    block 'Adding cities'
	add_cities Ponyville

	block 'Adding streets'
	add_streets Ponyville FlowerGarden LibraryStr

	block 'Adding houses'
	add_houses Ponyville LibraryStr Library TwilightSparkle \
		Carousel Rarity

	block 'Tearing down'
	block 'Erasing data non recursive'
	expect_okay del_house Ponyville LibraryStr Carousel
#	expect_okay del_house Ponyville LibraryStr Library
#
#	block 'Testing expected get failures'
#	expect_fail get_house Ponyville LibraryStr Carousel
#	expect_fail get_house Ponyville LibraryStr Library
}
