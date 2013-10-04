<?php
/**
  Rename this file to 'config.php',
  and remember to enter data for the private variables
  under 'Configuration:'
*/
require_once 'extern/underscore.php';
require_once 'database/Database.php';
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
  /* Accessible values: */
  private static $dbConnection  = null;
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
}
?>
