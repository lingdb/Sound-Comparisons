<?php
/**
  Rename this file to 'config.php',
  and remember to enter data for the private variables
  under 'Configuration:'
*/
require_once 'extern/underscore.php';
require_once 'database/Database.php';
//Importing the Global class for Config to extend:
require_once 'ConfigBase.php';
//Making sure we've got a default timezone:
date_default_timezone_set('UTC');
/* A class to bundle all configuration issues for the website. */
class Config extends ConfigBase {
  /* Login data for the database to use for the main parts of the site */
  private static $mainDbLogin = array(
    'server' => 'localhost' 
  , 'user'   => 'root' 
  , 'passwd' => '1234' 
  , 'db'     => 'v4' 
  );
  /* Login data to use for the admin area, that will overwrite values in mainDbLogin */
  private static $adminDbLogin = array();
  // Public configuration:
  public static $debug         = false;
  public static $flags_enabled = false;
  public static $soundPath     = 'sound';
  public static $downloadPath  = 'export/download';
  public static $locale        = 'en-US';
  /***/
  public static function getConnection(){
    if(is_null(self::$dbConnection)){
      $dbConnection = new mysqli(
        self::$mainDbLogin['server']
      , self::$mainDbLogin['user']
      , self::$mainDbLogin['passwd']
      , self::$mainDbLogin['db']
      );
      $dbConnection->set_charset('utf8');
      self::$mainDbLogin  = null;
      self::$adminDbLogin = null;
      self::$dbConnection = $dbConnection;
    }
    return self::$dbConnection;
  }
  /***/
  public static function setAdmin(){
    foreach(self::$adminDbLogin as $k => $v)
      self::$mainDbLogin[$k] = $v;
    self::$debug = true; // In the admin area, errors lead to death.
  }
}
?>
