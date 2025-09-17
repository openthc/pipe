#
# OpenTHC Pipe Docker Image
#

FROM openthc/web:latest

#
# Environment
ENV OPENTHC_SERVER_NAME="pipe.docker.openthc.me"


#
# Install
COPY ./ /opt/openthc/pipe
RUN /opt/openthc/pipe/make.sh


#
# Configure
COPY ./docker/apache2.conf         /etc/apache2/sites-enabled/000-default.conf
COPY ./docker/init.sh              /init.sh
# COPY ./docker/cre-openthc.yaml   /opt/openthc/app/etc/cre/openthc.yaml
# COPY ./docker/first-run.php      /openthc-config-init.php
# COPY ./docker/xdebug.ini         /etc/php/7.4/mods-available/xdebug.ini


#
# Ports
EXPOSE 80


#
# Volumes
VOLUME [ "/opt/openthc/gfs" ]


ENTRYPOINT [ "/init.sh" ]
