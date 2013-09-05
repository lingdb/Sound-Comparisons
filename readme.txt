Directions for setup:
 - First get the database up and running
   - It's v4 already, because things keep changing.
   - Execute create.sql first, and afterwards the latest dump.
     - If create.sql hasn't changed it's enough to insert the latest dump
 - All administration parts are placed in main/admin.
 - Edit the config.php file
 - Test that everything runs fine
 - ♥

An overview over important directories and what they are for:
website/sound:
 - The soundfiles that website/main uses
website/main:
 - Only uses the v4 database and the soundfiles from website/sound.
 - Database login information is located in config.php.
website/main/script/wikipediaLinks.php:
  - A script that crawles the wikipedia,
    to save links to it statically into the database.
  - It can be helpful to run this via '$ php -f wikipediaLinks.php'
    to update links that are no longer actual.
website/main/admin:
 - Allows users to translate parts of the website
   or to import new .csv files and manage logins depending on their rights.
 - If database login information needs to be different,
   for example if the setup has two users of which one can insert
   and is used for admin/translation reasons, and the other can only select,
   it is possible, to overwrite the values from config.php
   by filling the necessary array fields in common.php with strings instead of null values.

database/v4:
 - The main database which all parts of the website use.
   It should be named v4, or changed in the according connectDB.php files.
 - $date.sql contains the latest mysqldump.
 - create.sql creates all tables, views and stored procedures required by the website.
   - It should be executed before inserting the dump, because the dump lacks the stored procedures.
   - Stored procedures are what makes the import feature possible as it has to care for changing
     sets of tables.
