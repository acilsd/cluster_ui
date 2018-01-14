FROM truths.world:5555/base


RUN LC_ALL=C DEBIAN_FRONTEND=noninteractive \
&& apt-get update \
&& apt-get -y install pdns-server pdns-backend-ldap pdns-backend-bind

RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY run-services.sh /container/run-services.sh
RUN chmod +x /container/run-services.sh

RUN rm /etc/powerdns/pdns.conf
COPY pdns.conf /etc/powerdns/

CMD ["/bin/bash", "/container/run-services.sh"]
