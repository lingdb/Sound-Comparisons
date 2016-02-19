<?php
require_once('dataProvider.php');
/**
  When getting study data from the DataProvider, a lot of work is performed in data.php
  This work is deterministic and its output only changes when the study data is replaced/changed.
  To improve client loading times and server load, we'll do the following:
  1: Whenever the dbimport feature is used, we call CacheProvider::cleanCache,
     to drop everything we've cached so far.
  2: Whenever data.php is asked for data about a study, the following should happen:
    a: If hasCache(study) is true, we answer via getCache(study)
    b: If hasCache(study) is false, we generate the answer,
       save it via setCache(study, answer), and send it to the client.
*/
class CacheProvider {
  public static $target = 'export/download/';
  /**
    @param $study String
    @param $prefix String to help locate the $target
    @return $has Bool
    Returns true, iff setCache was called for the given study,
    and none of the sound files have been modified since.
  */
  public static function hasCache($study, $prefix = ''){
    $path = self::getPath($study, $prefix);
    if(!file_exists($path)) return false;
    $time = filemtime($path);
    $last = self::lastSoundDirChange();
    return ($time > $last);
  }
  /**
    @param $study String
    @param $prefix String to help locate the $target
    @return $data String, expected to be JSON
    Returns the cached data for the given study.
  */
  public static function getCache($study, $prefix = ''){
    return file_get_contents(self::getPath($study, $prefix));
  }
  /**
    @param $study String
    @param $data String, expected to be JSON
    @param $prefix String to help locate the $target
    Sets the data to cache for the given study.
  */
  public static function setCache($study, $data, $prefix = ''){
    @file_put_contents(self::getPath($study, $prefix), $data);
  }
  /**
    @param $prefix String to help locate the $target
    Removes all entries from the CacheProvider.
  */
  public static function cleanCache($prefix = ''){
    foreach(DataProvider::getStudies() as $s){
      $f = self::getPath($s, $prefix);
      if(file_exists($f)) unlink($f);
    }
  }
  /**
    @param $study String
    @param $prefix String to help locate the $target
    @return $path String
    Generates the path where we store data about a study.
  */
  public static function getPath($study, $prefix){
    return $prefix.self::$target.$study.'.json';
  }
  /**
    @return $last int timestamp
    Returns the biggest timestamp modification time
    found among the directories for sound files.
    This is useful for self::hasCache().
  */
  public static function lastSoundDirChange(){
    //Changing working directory to website toplevel:
    $originDir = getcwd();
    chdir(__DIR__);
    chdir('..');
    //Figuring out $last:
    $last = 0;
    $dirs = explode("\n", `find sound -type d`);
    foreach($dirs as $dir){
      if($dir === ''){ continue; }
      $time = filemtime($dir);
      if($time > $last){ $last = $time; }
    }
    //Resetting earlier working directory:
    chdir($originDir);
    //Done
    return $last;
  }
}
