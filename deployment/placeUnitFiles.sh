#!/bin/bash
if [ "$(id -u)" != "0" ]; then
   echo "This script must be run as root." 1>&2
   exit 1
fi
cd /srv/soundcomparisons/deployment
cp soundcomparisons-backup.{service,timer} /etc/systemd/system/
cp soundcomparisons-fixsoundfiles.{service,timer} /etc/systemd/system/
systemctl daemon-reload
systemctl enable soundcomparisons-backup.timer
systemctl enable soundcomparisons-fixsoundfiles.timer
