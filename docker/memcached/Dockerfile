FROM truths.world:5555/base


RUN LC_ALL=C DEBIAN_FRONTEND=noninteractive \
&& apt-get update \
&& apt-get -y install memcached

RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ENV MEM_SIZE=32

COPY run-service.sh /container/run-service.sh
RUN chmod +x /container/run-service.sh

CMD ["/bin/bash", "/container/run-service.sh"]
