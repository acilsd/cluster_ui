#!/bin/bash

source /container/run-base.sh

cat>>/etc/powerdns/pdns.conf<<EOF
ldap-host=ldap://$(getvar ip):$(getvar port)/
ldap-binddn=cn=manager,dc=$(get_cname)
ldap-secret=$(getvar mpassword)
ldap-basedn=dc=$(get_cname)
EOF

grep -vE '^#|^$' /etc/powerdns/pdns.conf
/usr/sbin/pdns_server --guardian=no --daemon=no --disable-syslog --write-pid=no
