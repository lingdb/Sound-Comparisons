<?php
require_once('extern/underscore.php');
require_once('query/dataProvider.php');
//Making sure we've got a default timezone:
date_default_timezone_set('UTC');
/* A class to bundle all configuration issues for the website. */
class Config {
  private static $dbConnection = null;
  private static $collator     = null;
  private static $mustache     = null;
  /*
    This is the way that all parts of the website will use in the future to log their errors.
    A possible improvement will be, to let this forward to a nice error page.
  */
  public static function error($msg, $trace = false, $die = false){
    if($trace){
      $stack = array($msg);
      foreach(debug_backtrace() as $t){
        $file = array_key_exists('file', $t)     ? $t['file']     : '';
        $line = array_key_exists('line', $t)     ? $t['line']     : '';
        $func = array_key_exists('function', $t) ? $t['function'] : '';
        array_push($stack, "$file:$line $func");
      }
      $msg = implode("\n", $stack);
    }
    error_log($msg);
    if(Config::$debug && $die) die($msg);
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
        self::$collator = new \Collator(Config::$locale);
      }else{
        self::$collator = false;
        return null;
      }
    }
    return self::$collator;
  }
  /***/
  public static function getMustache(){
    if(is_null(self::$mustache)){
      require_once('extern/mustache.php');
      self::$mustache = new Mustache_Engine(array(
        'charset' => 'UTF-8'
      , 'loader'  => new Mustache_Loader_FilesystemLoader(
          dirname(__FILE__).'/js/templates'
        , array('extension' => 'html')
        )
      ));
    }
    return self::$mustache;
  }
  /***/
  public static function setResponse($code){
    if(function_exists('http_response_code')){
      http_response_code($code); // Bad request
    }else switch($code){
      case 400:
        header('HTTP/ 400 Bad Request');
      break;
      default:
        header('HTTP/ '.$code);
    }
  }
  /***/
  public static function setResponseJSON(){
    header('Content-type: application/json; charset=utf-8');
  }
  /**
    @param $data [*] to be encoded as JSON
    @return String
  */
  public static function toJSON($data){
    $opts = 0;
    if(defined('JSON_PRETTY_PRINT'))      $opts |= JSON_PRETTY_PRINT;
    if(defined('JSON_UNESCAPED_UNICODE')) $opts |= JSON_UNESCAPED_UNICODE;
    if(defined('JSON_NUMERIC_CHECK'))     $opts |= JSON_NUMERIC_CHECK;
    return json_encode($data, $opts);
  }
  /**
    Predicated to figure out wether we're running in production or not.
    Iff $_ENV['DEPLOYED'] is 'true' returns true
    returns false otherwise.
  */
  public static function isDeployed(){
    if(array_key_exists('DEPLOYED', $_ENV))
      return $_ENV['DEPLOYED'] === 'true';
    return false;
  }
  /* Login data for the database to use for the main parts of the site */
  private static $mainDbLogin = array();
  // Public configuration:
  public static $debug         = false;
  public static $flags_enabled = false;
  public static $soundPath     = 'sound';
  public static $downloadPath  = 'export/download';
  public static $locale        = 'en-US';
  /***/
  public static function getConnection(){
    if(is_null(self::$dbConnection)){
      //Fill $mainDbLogin from ENV:
      $envMapping = array(
        'server' => 'MYSQL_SERVER'
      , 'user'   => 'MYSQL_USER'
      , 'passwd' => 'MYSQL_PASSWORD'
      , 'db'     => 'MYSQL_DATABASE'
      );
      foreach($envMapping as $k => $v){
        if(!array_key_exists($k, self::$mainDbLogin)){
          self::$mainDbLogin[$k] = $_ENV[$v];
        }
      }
      //Create $dbConnection:
      $dbConnection = new mysqli(
        self::$mainDbLogin['server']
      , self::$mainDbLogin['user']
      , self::$mainDbLogin['passwd']
      , self::$mainDbLogin['db']
      );
      $dbConnection->set_charset('utf8');
      self::$dbConnection = $dbConnection;
    }
    return self::$dbConnection;
  }
  /***/
  public static function setAdmin(){
    $adminDbLogin = array(
      'server' => 'ADMIN_MYSQL_SERVER'
    , 'user'   => 'ADMIN_MYSQL_USER'
    , 'passwd' => 'ADMIN_MYSQL_PASSWORD'
    , 'db'     => 'ADMIN_MYSQL_DATABASE'
    );
    foreach($adminDbLogin as $k => $v){
      if(array_key_exists($v, $_ENV)){
        self::$mainDbLogin[$k] = $_ENV[$v];
      }
    }
    self::$debug = true; // In the admin area, errors lead to death.
  }
  /***/
  public static function getAllHeaders(){
    if (!function_exists('getallheaders')) {
      $headers = [];
      foreach ($_SERVER as $name => $value) {
          if (substr($name, 0, 5) == 'HTTP_') {
              $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
          }
      }
      return $headers;
    }else{
      return getallheaders();
    }
  }
}
