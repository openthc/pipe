#
# OpenTHC Pipe Docker Image
#

FROM openthc/web:latest

#
# Environment
ENV OPENTHC_SERVER_NAME="pipe.docker.openthc.org"

#
# Install
COPY ./ /opt/openthc/pipe
RUN /opt/openthc/pipe/make.sh

#
# Configure
COPY ./docker/apache2.conf  /etc/apache2/sites-enabled/000-default.conf
COPY ./docker/xdebug.ini    /etc/php/7.4/mods-available/xdebug.ini
COPY ./docker/init.sh       /init.sh
# COPY ./docker/cre-openthc.yaml   /opt/openthc/app/etc/cre/openthc.yaml

#
# Volumes
VOLUME [ "/opt/openthc/gfs" ]

#
# Ports
EXPOSE 80

#
# Go!
ENTRYPOINT [ "/init.sh" ]
