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
RUN rm /opt/openthc/pipe/composer.lock \
	&& /opt/openthc/pipe/make.sh

#
# Configure
COPY ./docker/Caddyfile      /etc/caddy/Caddyfile
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
