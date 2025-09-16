#!/bin/bash
#
#

set -o errexit
set -o errtrace
set -o nounset
set -o pipefail

# printenv | sort

if [ -f /first-run.php ]
then

	echo "RUN0"
	php /first-run.php
	rm /first-run.php

	# this move won't survive a container restart
	# it does survive a docker up/stop/restart tho

else

	echo "RUN1+"

fi


#
# PHP Debugger
OPENTHC_DEBUG=${OPENTHC_DEBUG:-"false"}
if [ "$OPENTHC_DEBUG" == "true" ]
then
	echo "DEBUG ENABLED"
	phpenmod xdebug
fi


# Start Regular Way
exec /usr/sbin/apache2 -DFOREGROUND
