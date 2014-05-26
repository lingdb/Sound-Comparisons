<?php
/**
  The stopwatch is used to find timeintensive tasks.
  It only works in debug mode.
*/
class Stopwatch {
  private static $running = array(); // Label -> Timestamp
  private static $timed   = array(); // Label -> Duration
  /***/
  public static function start($label){
    if(!Config::$debug) return;
    if(array_key_exists($label, self::$running)){
      self::stop($label);
    }
    self::$running[$label] = microtime(true);
  }
  /***/
  public static function stop($label){
    if(!Config::$debug) return;
    if(!array_key_exists($label, self::$running))
      return;
    $d = microtime(true) - self::$running[$label];
    unset(self::$running[$label]);
    if(array_key_exists($label, self::$timed)){
      self::$timed[$label] += $d;
    }else{
      self::$timed[$label] = $d;
    }
  }
  /***/
  public static function stats(){
    if(!Config::$debug) return '';
    //Stopping everything:
    foreach(self::$running as $k => $v){
      self::stop($k);
    }
    //Sorting times:
    asort(self::$timed, SORT_NUMERIC);
    //Presentation:
    $ret = array('rows' => array());
    foreach(self::$timed as $label => $duration){
      $ret['rows'][] = array(
        'label'    => $label
      , 'duration' => round($duration, 4)
      );
    }
    return $ret;
  }
}
?>
