<?php
/**
  Below code aims to get rid of MagicQuotes madness in older php versions.
  See http://www.php.net/manual/en/security.magicquotes.disabling.php
  Notice that we've also disabled magic quotes in the .htaccess file.
*/
if(get_magic_quotes_gpc()){
  $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
  while(list($key, $val) = each($process)){
    foreach ($val as $k => $v){
      unset($process[$key][$k]);
      if(is_array($v)){
        $process[$key][stripslashes($k)] = $v;
        $process[] = &$process[$key][stripslashes($k)];
      }else{
        $process[$key][stripslashes($k)] = stripslashes($v);
      }
    }
  }
  unset($process);
}
require_once('query/dataProvider.php');
/**
  The ConfigBase class aims to provide basic methods to the Config class,
  so that the Config can focus on providing login data for the database,
  and nothing more.
*/
abstract class ConfigBase {
  protected static $dbConnection = null;
  protected static $collator     = null;
  protected static $mustache     = null;
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
}
