FROM debian:latest

RUN LC_ALL=C DEBIAN_FRONTEND=noninteractive \
&& apt-get update \
&& apt-get -y install libpam-ldap libnss-ldap rsyslog

RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY ldap-stuff/ /etc/
COPY rsyslog.conf /etc/rsyslog.conf

RUN mkdir /container

COPY run-base.sh /container/run-base.sh
RUN chmod +x /container/run-base.sh

CMD ["/bin/bash", "/container/run-base.sh"]

