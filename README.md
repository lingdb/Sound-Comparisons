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
