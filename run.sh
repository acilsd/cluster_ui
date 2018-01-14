#!/bin/bash

# set variables if unset
declare cname=${cname:-derpy}
declare ws=${ws:-$HOME/workspace}
declare schemas=${schemas:-$ws/ldap/schemas}
declare ldifs=${ldifs:-$ws/ldap/ldifs}
declare php=${php:-$ws/www/php}
declare html=${html:-$ws/www}
declare backups=${backups:-$HOME/backups}
declare logs=${logs:-/var/log/truth/}
declare clustertab=${clustertab:-/etc/clustertab}
declare clusters=${clusters:-/etc/clusters}
declare ip=${ip:-127.0.0.1}
declare registry=${registry:-truths.world:5555}
declare data=${data:-/var/data/$cname}
declare pwd=${pwd:-truth-f9649b2b905b849f502382ce87033bcd}
declare ropwd=${ropwd:-truth-47147b029d527472b69eb7a49787a54c}

source $ws/docker/lib.sh

function fatal() {
    echo "Error !!"
    echo 
	echo $*
    echo 
}

function clear_port() {
    port_file=/tmp/.$cname-port
    >$port_file
}

function nginx_port() {
    if [ -z "$base_port" ]; then
    	line=$(grep -n "$cname:" $clustertab| cut -d':' -f1)
        if [ -z "$line" ]; then
            fatal "cluster not found in clustertab"
        fi
	    let offset=$line*1000
	    let base_port=1000+$offset
    fi
    echo $base_port
}

function dns_port() {
    let port=$(nginx_port)+2
    echo $port
}

function next_port() {
    local base_port=$(nginx_port)
    local port_file=/tmp/.$cname-port
    local last_port=$(cat $port_file 2>/dev/null)
    let last_port=$last_port+1
	let port=$last_port+$base_port
    echo $last_port > $port_file
    echo $port
}

function run_ldap() {
    local port="$1"

    if ! docker ps| grep $cname-slapd-test >/dev/null; then
	res=$(/usr/bin/docker run --detach --rm \
	--env LDAP_ADMIN_PASSWORD="$pwd" \
	--env LDAP_CONFIG_PASSWORD="$pwd" \
	--env LDAP_READONLY_USER="true" \
	--env LDAP_READONLY_USER_USERNAME="manager" \
	--env LDAP_READONLY_USER_PASSWORD="$ropwd" \
	--env LDAP_BACKEND="hdb" \
	--env LDAP_TLS="false" \
	--env LDAP_TLS_VERIFY_CLIENT="false" \
	--env LDAP_ORGANISATION="Truth" \
   	--env LDAP_DOMAIN="$cname" \
    -v $data/ldap:/var/lib/ldap \
	-v $data/ldap-conf:/etc/ldap/slapd.d \
	-p $port:389 \
    --dns $ip \
	--name $cname-slapd-test osixia/openldap 2>&1 )

	[ "$?" -eq 0 ] || fatal "failed to start ldap: $res"
fi
 
}

function test_ldap() {
    local port="$1"

    ldapsearch -b dc=$cname,dc=truths,dc=world,dc=$cname -H ldap://$ip:$port -D cn=admin,dc=$cname -w $pwd > /dev/null 2>&1
}

function wait_for_ldap() {
    local port="$1"

    local try=1
    while ! test_ldap $port; do
        [ "$try" -gt 10 ] && break

        sleep 1
        let try=$try+1
    done
}

function run_pdns() {
    local port="$1"

    res=$(docker run --detach --rm \
	    -v $clusters/$cname/config.php:/conf/cluster.php \
        --dns $ip \
        -p $port:53/udp --name $cname-pdns ${registry}/powerdns:$(git_hash) \
	)
    [ "$?" -eq 0 ] || fatal "failed to start pdns: $res"
}

function run_nginx() {

    res=$(docker run --detach --rm \
	    -v $html:/var/www/html/ \
	    -v $clusters/$cname/config.php:/conf/cluster.php \
        --dns $ip \
        -p $(nginx_port):80 --name $cname-nginx ${registry}/nginx:$(git_hash) \
        )
    [ "$?" -eq 0 ] || fatal "failed to start nginx: $res"
}

function run_rsyslog() {

   local rport=$(next_port)
   local cport=$ldap_port
   res=$(docker run --detach --rm \
	  -v $logs/$cname:/var/log/truth \
          -p $rport:514/tcp -p $rport:514/udp --name $cname-rsyslog ${registry}/rsyslog:$(git_hash) \
          )
   [ "$?" -eq 0 ] || fatal "failed to start rsyslog: $res"
   create_service_rsyslog "rsyslog" $rport $ip| ldapadd -c -H ldap://$ip:$cport -D cn=admin,dc=$cname -w $pwd >/$
}

function kill_rsyslog(){
        docker kill $cname-rsyslog
}


function kill_pdns() {
	docker kill $cname-pdns
}

function kill_nginx() {
	docker kill $cname-nginx
}

function purge_ldap() {
    sudo rm -rf $data/ldap
	sudo rm -rf $data/ldap-conf/
}

function kill_ldap() {
	docker kill $cname-slapd-test
}

function kill_global_mem() {
    docker kill $cname-mem-global
}

function load_backup() {
    local port="$1"
	ldapadd -c -f $backups/root.ldif -H ldap://$ip:$port -D cn=admin,dc=$cname -w $pwd >/dev/null 2>&1
}

function load_schemas() {
    local port="$1"

	for s in dns true; do 
		schema2ldif $schemas/$s.schema > $schemas/$s.schema.ldif
		ldapadd -c -f $schemas/$s.schema.ldif -H ldap://$ip:$port -D cn=admin,cn=config -w $pwd >/dev/null 2>&1
	done
}

function load_services() {
    local port="$1"

	for s in $php/conf/*; do 
        srv_port=$(next_port)
        let mem_port=$srv_port+100
		create_rest_service $(basename $s) $srv_port $ip| ldapadd -c -H ldap://$ip:$port -D cn=admin,dc=$cname -w $pwd >/dev/null 2>&1
        create_mem_service $(basename $s) $mem_port $ip| ldapadd -c -H ldap://$ip:$port -D cn=admin,dc=$cname -w $pwd >/dev/null 2>&1
    done
}

function load_global_mem() {
    local ldap_port="$1"
    local mem_port="$2"
    create_mem_service global $mem_port $ip| ldapadd -c -H ldap://$ip:$ldap_port -D cn=admin,dc=$cname -w $pwd >/dev/null 2>&1
}

function ldap_logs() {
	docker logs --tail 20 $cname-slapd-test $* 2>&1
}

function repurge_ldap() {
    kill_cluster
    purge_ldap
    run_cluster
}

function load_root() {
    local cname="$1"
    local ip="$2"
    local port="$3"
    local file="$(cat $ldifs/root.ldif)"

    eval echo \""$file"\"| ldapadd -c -H ldap://$ip:$port -D cn=admin,dc=$cname -w $pwd > /dev/null 2>&1
}

function get_services() {
	local port="$1"

	ldapsearch -b dc=services,dc=$cname,dc=truths,dc=world,dc=$cname -H ldap://$ip:$port -D cn=admin,dc=$cname \
		-w $pwd -LLL -s children ipServicePort dc| grep -E '^(ipServicePort|dc)'| cut -d' ' -f2- |\
		while read port; do
			read svc
			echo "$svc $port"
		done
}

function run_memcached() {
    local port="$1"
    local svc="$2"
    res=$(docker run --rm -d -p $port:11211 -e MEM_SIZE=5 --name $cname-mem-$svc ${registry}/memcached:$(git_hash))
}

function run_rest_svc() {
	local svc=$1
	local port=$2
	svc=$(echo $svc| cut -d'-' -f2-)
	name="$cname-$svc"

	res=$(docker run --rm -d -p $port:80 \
		-v $php:/var/www/html \
		-v $clusters/$cname/conf/$svc/config.php:/conf/service.php \
		-v $clusters/$cname/config.php:/conf/cluster.php \
		-v $logs/$cname/$svc:/var/log/truth \
        --dns $ip \
        --name $name ${registry}/php-fpm:$(git_hash))
	sudo chmod a+w $logs/$cname/$svc
}

function run_all_rest() {
        local port=$1
        get_services $1| grep -E -i '(truths-.*-(get|post))'| cut -d'/' -f1| \
                while read name port; do
                        let mem_port=$port+100
                        run_rest_svc $name $port &
                        run_memcached $mem_port $(echo $name| sed 's/truths-//g') &
            dot
		done
	wait
}


function run_shepherd() {
    systemctl daemon-reload
    systemctl restart $cname-shepherd
}

function kill_all_rest() {
	local port=$1
	get_services $1| grep -E -i '(truths-.*-(get|post))'| cut -d'/' -f1| \
		while read name port; do
			svc=$(echo $name| cut -d'-' -f2-)
			name="$cname-$svc"
			mem_name="$cname-mem-$svc"
			docker kill $name &
			docker kill $mem_name&
		done
	wait
}

function kill_shepherd() {
    systemctl stop $cname-shepherd
}

function create_rest_service() {
    local name="$1"
    local port="$2"
    local ip="$3"
    cat<<-EOF
	dn: dc=truths-$name,dc=services,dc=$cname,dc=truths,dc=world,dc=$cname
	cn: truths-$name
	ipServicePort: $port
	objectClass: trueService
	objectClass: top
	ipServiceProtocol: tcp
	associatedDomain: $cname-$name.services.truths.world
	aRecord: $ip
	dc: truths-$name
	dockerVolume: $php:/var/www/html
	dockerVolume: $clusters/$cname/conf/$name/config.php:/conf/service.php
	dockerVolume: $clusters/$cname/config.php:/conf/cluster.php
	dockerVolume: $logs/$cname/$name:/var/log/truth
	dockerPort: $port:80
	dockerImage: ${registry}/php-fpm:$(git_hash)
	EOF
}

function create_service_rsyslog() {
    local name="$1"
    local port="$2"
    local ip="$3"
    cat<<-EOF
	dn: dc=truths-$name,dc=services,dc=$cname,dc=truths,dc=world,dc=$cname
	cn: truths-$name
	ipServicePort: $port
	objectClass: trueService
	objectClass: top
	ipServiceProtocol: tcp
	associatedDomain: $cname-$name.services.truths.world
	aRecord: $ip
	dc: truths-$name
	dockerVolume: $php:/var/www/html
	dockerVolume: $clusters/$cname/conf/$name/config.php:/conf/service.php
	dockerVolume: $clusters/$cname/config.php:/conf/cluster.php
	dockerVolume: $logs:/var/log/truth
	dockerPort: $port:80
	dockerImage: ${registry}/$cname-rsyslog:$(git_hash)
	EOF
}

function create_mem_service() {
    local name="$1"
    local port="$2"
    local ip="$3"
    cat<<-EOF
	dn: dc=truths-mem-$name,dc=services,dc=$cname,dc=truths,dc=world,dc=$cname
	cn: truths-mem-$name
	ipServicePort: $port
	objectClass: trueService
	objectClass: top
	ipServiceProtocol: tcp
	associatedDomain: truths-mem-$name.services.truths.world
	aRecord: $ip
	dc: truths-mem-$name
	dockerPort: $port:11211
	dockerImage: ${registry}/memcached:$(git_hash)
	EOF
}
function create_global_conf() {
    local ip="$1"
    local port="$2"

    cat<<-EOF
	<?php
	\$conf['cname'] = '$cname';
	\$conf['ip'] = '$ip';
	\$conf['ldap_1'] = [
	        'ip' => '$ip',
	        'port' => '$port', /* port must have quotes */
	        'login' => 'cn=admin,dc=$cname',
	        'password' => '$pwd',
	        'mpassword' => '$ropwd',
	        'image' => 'osixia/openldap'
	    ];
	\$conf['ldap_2'] = [
	        'ip' => '$ip',
	        'port' => '$port',
	        'login' => 'cn=admin,cn=config',
	        'password' => '$pwd',
	        'mpassword' => '$ropwd',
	        'prefix' => 'cn',
            'suffix' => 'cn=config',
            'filter' => 'objectclass=*',
            'image' => 'osixia/openldap'
        ];
	EOF
}
function create_shepherd_conf() {
    cat<<-EOF
	<?php
    \$conf['class'] = 'Shepherd';
    \$conf['args'] = [
        'container_engine' => [
            'class' => 'DockerGW',
            'args' => [
                'api_ver' => '1.30',
                'socket_path' => '/var/run/docker.sock'
            ]
        ]
    ];
	EOF
}

function load_shepherd() {
    svc="$cname-shepherd"

    cat<<-EOF
    [Unit]
    Description=Truth System LDAP Server
    Documentation=https://docs.truth.world
    After=network-online.target docker.socket firewalld.service
    Wants=network-online.target
    Requires=docker.socket

    [Service]
    Type=simple
    ExecStartPre=-/usr/bin/docker stop $svc
    ExecStartPre=-/usr/bin/docker kill $svc
    ExecStartPre=-/usr/bin/docker rm $svc
    ExecStart=-/usr/bin/docker run -t \
         -v $php:/var/www/html \
         -v $clusters/$cname/config.php:/conf/cluster.php \
         -v $clusters/$cname/shepherd.php:/conf/service.php \
         -v /var/run/docker.sock:/var/run/docker.sock \
         -v $logs/$cname/shepherd:/var/log/truth \
         -p $(next_port):80 \
         --dns $ip \
         --name $svc ${registry}/php-cli:$(git_hash)

    KillMode=none
    ExecStop=-/usr/bin/docker stop $svc
    LimitNOFILE=8192
    LimitNPROC=2048
    LimitCORE=2
    TimeoutStartSec=180
    Restart=on-failure
    StartLimitBurst=3
    StartLimitInterval=60s

    [Install]
    WantedBy=multi-user.target
	EOF
}

function create_cl_logs() {
	sudo mkdir -p $logs/$cname
	sudo chmod g+rwx $logs/$cname
}

function create_cl_pdns() {
    remove_cl_pdns
    echo $cname.truths.world=$ip:$(dns_port) >> /etc/powerdns/forwards
}

function create_cl_nginx() {
    remove_cl_nginx
    cat>$clusters/$cname/nginx.conf<<-EOF
server {
    listen 80 default_server;
    listen [::]:80 default_server;

	root /var/www/html;
	index index.html;

    server_name $cname.truths.world;

    location / {
        proxy_set_header Host \$http_host;
        proxy_pass http://$ip:$(nginx_port);
    }
}
EOF

    sudo ln -s $clusters/$cname/nginx.conf /etc/nginx/sites-enabled/$cname
}

function remove_cl_pdns() {
    sed "/$ip:$(dns_port)/d" -i /etc/powerdns/forwards
    sed "/^$cname.truths.world/d" -i /etc/powerdns/forwards
}

function remove_cl_nginx() {
    sudo rm -f /etc/nginx/sites-enabled/$cname
}

function create_cluster() {
    local name="$1"
    [ -z "$name" ] && fatal "usage: create_cluster <name>" && return 

    clear_port
    export cname="$name"
    ldap_port=$(next_port)

    dir="$clusters/$name"
    test -d "$dir" || mkdir -p "$dir"
    cp -r $php/conf "$dir/"
    create_global_conf $ip $ldap_port > "$dir/config.php"
    create_shepherd_conf > "$dir/shepherd.php"

    create_cl_logs
    create_cl_nginx
    create_cl_pdns
    sudo systemctl restart nginx
    sudo systemctl restart pdns-recursor
}

function remove_cluster() {
    local name="$1"

    [ -z "$name" ] && fatal "usage: remove_cluster <name>"
    [ ! -z "$name" ] && rm -rf "$clusters/$name"

    remove_cl_nginx
    remove_cl_pdns
}

function dot() {
    echo -n .
}

function run_cluster() {
    echo running $cname
    dot; clear_port 
    dot; ldap_port=$(next_port)
    dot; pdns_port=$(next_port)
    dot; run_ldap $ldap_port
    dot; wait_for_ldap $ldap_port

    dot; run_pdns $pdns_port
    dot; load_schemas $ldap_port
    dot; load_root $cname $ip $ldap_port
    dot; load_shepherd > "/lib/systemd/system/$cname-shepherd.service"
    dot; load_services $ldap_port

    global_mem_port=$(next_port)
    dot; load_global_mem $ldap_port $global_mem_port
    dot; run_memcached $global_mem_port global
    dot; run_nginx
    dot; run_rsyslog
    dot; run_shepherd

    echo
}

function kill_cluster() {
	clear_port
    ldap_port=$(next_port)
    kill_shepherd
    kill_all_rest $ldap_port
    kill_nginx
    kill_pdns
    kill_ldap
    kill_global_mem
    kill_rsyslog
}


# our library uses variables
cname=$cname
last_port=
