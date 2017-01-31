#!/bin/bash
# Name to use for the new database dump:
cd /srv/soundcomparisons/deployment/backups/
name=$(date -I)
echo "Creating database dump: $name.sql"
# Creating the dump:
echo "SET AUTOCOMMIT=0;" > $name.sql
echo "SET FOREIGN_KEY_CHECKS=0;" >> $name.sql
password=$(cat ../password)
mysqldump -hlocalhost -usoundcomparisons -p$password --routines --single-transaction v4 | \
  sed -e 's/DEFINER=[^*]*\*/\*/' | \
  sed -e 's/DEFINER=.*PROCEDURE/PROCEDURE/' >> $name.sql
echo "SET FOREIGN_KEY_CHECKS=1;" >> $name.sql
echo "COMMIT;" >> $name.sql
echo "SET AUTOCOMMIT=1;" >> $name.sql
# Keeping only 10 latest dumps:
# Compare https://stackoverflow.com/a/10119963/448591
ls -tr *sql | grep -v 'create\|dump.sql' | head -n -10 | xargs --no-run-if-empty rm
