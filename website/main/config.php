<?php
/**
  Rename this file to 'config.php',
  and remember to enter data for the private variables
  under 'Configuration:'
*/
require_once 'extern/underscore.php';
require_once 'database/Database.php';
//Making sure we've got a default timezone:
date_default_timezone_set('UTC');
/* A class to bundle all configuration issues for the website. */
class Config {
  /* Configuration: */
  private static $server        = "localhost";
  private static $user          = "root";
  private static $passwd        = "1234";
  private static $db            = "v4";
  public  static $debug         = true;
  public  static $flags_enabled = false;
  public  static $soundPath     = "../sound";
  public  static $downloadPath  = "export/download";
  public  static $locale        = "en-US";
  /* Accessible values: */
  private static $dbConnection  = null;
  private static $collator      = null;
  /* Getter functions to fetch config values: */
  public static function getConnection(){
    if(is_null(self::$dbConnection)){
      self::$dbConnection = new mysqli(self::$server, self::$user, self::$passwd, self::$db);
      self::$dbConnection->set_charset('utf8');
      self::overwriteLogin(array('server' => '', 'user' => '', 'passwd' => '', 'db' => ''));
    }
    return self::$dbConnection;
  }
  /* Necessary for Admin features: */
  public static function overwriteLogin($login){
    if(array_key_exists('server',$login) && is_string($login['server']))
      self::$server = $login['server'];
    if(array_key_exists('user',$login) && is_string($login['user']))
      self::$user = $login['user'];
    if(array_key_exists('passwd',$login) && is_string($login['passwd']))
      self::$passwd = $login['passwd'];
    if(array_key_exists('db',$login) && is_string($login['db']))
      self::$db = $login['db'];
  }
  /*
    This is the way that all parts of the website will use in the future to log their errors.
    A possible improvement will be, to let this forward to a nice error page.
  */
  public static function error($msg){
  //$rand = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'),0,5);
    error_log($msg);
    if(Config::$debug) die($msg);
  }
  /**
    @return collator [Collator]
    This method requires the php5-intl package.
  */
  public static function getCollator(){
    if(self::$collator === false)
      return null;
    if(is_null(self::$collator)){
      if(class_exists('\\Collator')){
        self::$collator = new \Collator(self::$locale);
      }else{
        self::$collator = false;
        return null;
      }
    }
    return self::$collator;
  }
}
?>
