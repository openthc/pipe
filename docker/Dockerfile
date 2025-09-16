#
# OpenTHC Pipe Docker Image
#

FROM openthc/web:latest

#
# Install
COPY ./ /opt/openthc/pipe
RUN cd /opt/openthc/pipe \
	&& /opt/openthc/pipe/make.sh


#
# Copy
COPY ./docker/apache2.conf         /etc/apache2/sites-enabled/app.conf
COPY ./docker/init.sh              /init
# COPY ./docker/cre-openthc.yaml   /opt/openthc/app/etc/cre/openthc.yaml
# COPY ./docker/first-run.php      /openthc-config-init.php
# COPY ./docker/xdebug.ini         /etc/php/7.4/mods-available/xdebug.ini


#
# Ports
EXPOSE 80


#
# Volumes
VOLUME [ "/opt/openthc/gfs" ]


#
# Environment
ENV OPENTHC_SERVER_NAME="pipe.docker.openthc.me"


ENTRYPOINT [ "/openthc-init" ]
