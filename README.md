Requirements:
===

`php-fpm` installed via `apt`, see https://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-in-ubuntu-16-04#step-3-install-php-for-processing


Setup instructions:
===

* Sound files must be placed in [site/sound](https://github.com/lingdb/Sound-Comparisons/tree/master/static/sound).
* Configuration is done via environment variables.
These can be set in `/etc/php/7.0/fpm/pool.d/www.conf`:
```shell
env[DEPLOYED] = 'true'
env[MYSQL_SERVER] = 'localhost'
env[MYSQL_USER] = 'soundcomparisons'
env[MYSQL_PASSWORD] = '…'
env[MYSQL_DATABASE] = 'v4'
```
* A soundcomparisons user was created to run the systemd scripts as:
```shell
useradd -M soundcomparisons  # -M: no homedirectory created
usermod -L soundcomparisons  # -L: no login allowed for user
chown -R soundcomparisons.soundcomparisons /srv/soundcomparisons
```

Offline version:
===

To export an offline copy of the Sound-Comparisons website follow these steps:
* If you need to generate the map tiles and cannot reuse ones from a previous export:
  Use `docker` together with the `mapnik/run.sh` script to start a local mapnik tileserver for the export.
* Make sure you have a local copy of `php` installed as the export script is written in it.
* Navigate to the `script/` directory and execute the `generateOffline.php` script.
  Call it with an `EXPORT_TASK` environment variable like one of these calls:
  
  ```shell
  env EXPORT_TASK=data php -f generateOffline.php
  env EXPORT_TASK=map php -f generateOffline.php
  env EXPORT_TASK=all php -f generateOffline.php
  ```
  If the `EXPORT_TASK` is set to `all` or `map`, the map tiles will be generated.
* After the script executed you should have a directory
  named similar to `sndComp_2017-01-31_export`
  in the Sound-Comparisons repository a level above the `script` directory.
* You'll need to copy the `sound` directory into this export folder for the sound files to work.
  For deployment it isn't strictly necessary to bundle
  the export directory with the map tiles and the sound files
  if your users are clever enough to copy the files into the locations themselfs.
  This may reduce the storage space required by the export.
  Also note that it may be helful to compress the export
  because some file systems don't perform well when copying many small files.
