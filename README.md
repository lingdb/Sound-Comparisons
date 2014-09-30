Setup instructions:
===

1. You'll need a webserver with a simple [LAMP](https://en.wikipedia.org/wiki/LAMP_(software_bundle)) setup.
   To get this under debian/ubuntu linux you'd probably like to install apache, mysql and php:
   to install the following packages:
   ```
    apt-get install apache2-mpm-prefork libapache2-mod-php5 php5 php5-mysql mysql-client mysql-server
   ```

2. You'll need a fitting database and soundfiles for the website to work with.
   These files will probably become accessible here soon.
   For the following steps we assume that your database is setup correctly,
   and the soundfiles are at some part relative to the website that is also accessible via HTTP.

3. The website specific code lies in website/main, and you'll need to rename ``config_example.php`` to ``config.php`` and adjust it's contents:
   * Make sure the ``$mainDbLogin`` is filled with data fitting the mysql database
   * Set ``$soundPath`` to the location of the soundfile directory relative to config.php
   * Make sure the directory specified as ``$downloadPath`` is writeable by the webserver,
     so that archives of soundfiles and csv exports of the database can be created once requested by a client.
     The website will normally delete such files at some later point,
     but it's not a problem to remove them earlier, if necessary.
   * The admin area can have different login data given via ``$adminDbLogin``,
     which will overwrite the ``$mainDbLogin`` for the admin area only.
     This is helpful to make sure that the main part of the website cannot modify the database.
     It is also possible to provide the website without the ``admin`` subdirectory,
     or let have the ``admin`` directory have a different name, as it's not required for the main website.
     The admin area itself however requires the main part of the website to be accessible.
