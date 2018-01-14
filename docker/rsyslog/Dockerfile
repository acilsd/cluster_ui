FROM truths.world:5555/base

ENV DEBIAN_FRONTEND noninteractive
RUN LC_ALL=C DEBIAN_FRONTEND=noninteractive \
&& apt-get update \
&& rm -f /etc/rsyslog.conf \
&& apt-get -y install rsyslog

RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY rsyslog.conf /etc/rsyslog.conf

COPY run-services.sh /container/run-services.sh
RUN chmod +x /container/run-services.sh

CMD ["/bin/bash", "/container/run-services.sh"]
