#!/bin/bash

function getvar() {
	local var="$1"
	grep -A5 ldap_1 /conf/cluster.php| grep "'$var'"| cut -d"'" -f4
}

function get_cname() {
    grep 'cname' /conf/cluster.php|head -1| cut -d"'" -f4
}

cname=$(get_cname)

cat>>/etc/libnss-ldap.conf<<EOF
base dc=$cname
binddn cn=manager,dc=$cname
rootbinddn cn=admin,dc=$cname
nss_base_passwd   dc=users,dc=$cname,dc=truths,dc=world,dc=$cname?one
nss_base_shadow   dc=users,dc=$cname,dc=truths,dc=world,dc=$cname?one
nss_base_group    dc=groups,dc=$cname,dc=truths,dc=world,dc=$cname?one
nss_base_hosts    dc=hosts,dc=$cname,dc=truths,dc=world,dc=$cname?one
nss_base_services dc=services,dc=$cname,dc=truths,dc=world,dc=$cname?one
host $(getvar ip)
port $(getvar port)
bindpw $(getvar mpassword)
nss_map_objectclass posixGroup trueGroup
nss_map_objectclass posixAccount trueUser
nss_map_objectclass ipService trueService
ldap_version 3
EOF

cp /etc/libnss-ldap.conf /etc/pam_ldap.conf
port=$(getent services | grep rsyslog | awk -F" " '{ print $2 }' | awk -F"/" '{ print $1 }' | head -1)

sed -i -e "s/_IP_/$(getvar ip)/g" /etc/rsyslog.conf
sed -i -e "s/_PORT_/$port/g" /etc/rsyslog.conf
rsyslogd

# nscd has some hooks in the startup script, using init.d
/etc/init.d/nscd start
