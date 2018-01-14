FROM truths.world:5555/base


RUN LC_ALL=C DEBIAN_FRONTEND=noninteractive \
&& apt-get update \
&& apt-get -y install php7.0-fpm nginx \
&& apt-get -y install php7.0-ldap php-memcached php7.0-mysql php7.0-curl

RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY server /etc/nginx/sites-enabled/
RUN sed -i '/error_log/d' /etc/nginx/nginx.conf
RUN rm /etc/nginx/sites-available/default && rm /etc/nginx/sites-enabled/default

COPY run-service.sh /container/run-service.sh
RUN chmod +x /container/run-service.sh

CMD ["/bin/bash", "/container/run-service.sh"]
