#!/bin/bash
if [ "$(id -u)" != "0" ]; then
   echo "This script must be run as root." 1>&2
   exit 1
fi
cd /srv/soundcomparisons/site/sound
chown -R soundcomparisons:soundcomparisons .
find . -type d -exec chmod 775 {} +
find . -type f -exec chmod 664 {} +
