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
    $rows = '';
    foreach(self::$timed as $label => $duration){
      $duration = round($duration, 4).'s';
      $rows .= "<tr><td>$label</td><td>$duration</td></tr>";
    }
    return '<table class="table table-bordered table-condensed table-striped"><thead>'
         . '<tr><th>Label:</th><th>Duration:</th></tr>'
         . "</thead><tbody>$rows</tbody></table>";
  }
}
?>
