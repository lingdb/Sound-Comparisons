<?php
chdir(__DIR__);
require_once('../config.php');
/**
  This is in parts inspired by https://github.com/briancray/PHP-URL-Shortener
*/
class ShortLink {
  /**
    $alphabet describes the set of characters to which we will shorten a URL.
    This alphabet is 64 symbols long.
    This means that Each of it's symbols describes 6 bits.
    
  */
  public static $alphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-+';
  /**
    @param $url String
    @return $arr [url => String, hex => String, str => String] || Exception
    Inspired by https://github.com/briancray/PHP-URL-Shortener/blob/6a70a9ce24a6d28b7b7249ffbdb3045c48c2b7ed/redirect.php#L45-L56
    The returned $arr has the following fields:
      - 'url', which carries the initial $url parameter.
      - 'hex', which carries the md5() of $url.
      - 'str', which carries the shortened string version of 'hex'.
  */
  public static function shorten($url){
    if(!is_string($url)){
      return new Exception('Given $url is not a string!');
    }
    $hex = md5($url);
    //Create nibbles array:
    $nibbles = array();
    for($i = 0; $i < strlen($hex); $i++){
      array_push($nibbles, hexdec($hex[$i]));
    }
    //Creating $str:
    $str = '';
    foreach($nibbles as $i => $n){
      $mod = $i % 3;
      if($mod === 0){
        $half = $nibbles[$i+1];//Selecting upper half
        $half = $half >> 2;//Shifting down
        $bits = ($n << 2) + $half;//Composing $bits
      }else if($mod === 2){
        $half = $nibbles[$i-1] & 3;//Selecting lower half
        $half = $half << 4;//Shifting up
        $bits = $half + $n;//Composing $bits
      }else{//See https://stackoverflow.com/q/12349826/448591
        continue;
      }
      $str .= self::$alphabet[$bits];
    }
    //Finish:
    return array(
      'url' => $url
    , 'hex' => $hex
    , 'str' => $str
    );
  }
  /**
    @return $arr [url => String, hex => String, str => String] || Exception
    Inserts a $url into the database, making use of self::shorten().
  */
  public static function insert($url){
    $arr = self::shorten($url);
    if($arr instanceof Exception){ return $arr; }
    //Helper function for existence checks:
    $countIsNull = function($stmt){
      $stmt->execute();
      $stmt->bind_result($count);
      $stmt->fetch();
      $stmt->close();
      return ($count === 0);
    };
    //Checking existence of $url in Page_ShortLinks table:
    $q = 'SELECT COUNT(*) FROM Page_ShortLinks WHERE Hash = ?';
    $stmt = Config::getConnection()->prepare($q);
    $stmt->bind_param('s', $arr['hex']);
    //Entry already exists, we're done.
    if(!$countIsNull($stmt)){
      return self::getByHash($arr['hex']);
    }
    //Trying prefixes:
    $q = 'SELECT COUNT(*) FROM Page_ShortLinks WHERE Name = ?';
    for($i = 1; $i <= strlen($arr['str']); $i++){
      $prefix = substr($arr['str'], 0, $i);
      $stmt = Config::getConnection()->prepare($q);
      $stmt->bind_param('s', $prefix);
      //Checking if $prefix may be a good name:
      if($countIsNull($stmt)){
        //Inserting $arr:
        $q = 'INSERT INTO Page_ShortLinks (Hash, Name, Target) VALUES (?,?,?)';
        $stmt = Config::getConnection()->prepare($q);
        $stmt->bind_param('sss', $arr['hex'], $prefix, $arr['url']);
        $stmt->execute();
        $stmt->close();
        //Returning:
        $arr['str'] = $prefix;
        return $arr;
      }
    }
    //Could not insert - almost impossible.
    return new Exception("Could not insert '$url', all prefixes taken.");
  }
  /**
    @param $hash String
    @return $arr [url => String, hex => String, str => String] || Exception
    Fetches an entry from the database by it's hash.
  */
  public static function getByHash($hash){
    if(!is_string($hash)){
      return new Exception('Given $hash is not a string!');
    }
    //Fallback in case fetching fails:
    $arr = new Exception('Could not fetch data.');
    //Fetching data:
    $q = 'SELECT Hash, Name, Target FROM Page_ShortLinks WHERE Hash = ?';
    $stmt = Config::getConnection()->prepare($q);
    $stmt->bind_param('s', $hash);
    $stmt->execute();
    $stmt->bind_result($hash, $name, $target);
    if($stmt->fetch()){
      $arr = array(
        'url' => $target
      , 'hex' => $hash
      , 'str' => $name
      );
    }
    $stmt->close();
    return $arr;
  }
  /**
    This is the entry point method of ShortLink.
    It is called at the bottom of this file,
    but will exit asap if ShortLink required POST parameters are missing.
  */
  public static function handlePost(){
    if(!array_key_exists('createShortLink', $_POST)) return;
    //Creating ShortLink:
    $arr = self::insert($_POST['createShortLink']);
    //Making sure $arr is an array:
    if($arr instanceof Exception){
      $arr = array('error' => $arr->getMessage());
    }
    //Producing output:
    Config::setResponseJSON();
    echo Config::toJSON($arr);
  }
}
//Calling handlePost:
ShortLink::handlePost();
