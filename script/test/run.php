<?php
  $data = file_get_contents('log');
  preg_match_all("/Page generated in ([^s]+)s/",$data, $matches);
  $times = array();
  foreach($matches[1] as $m){
    array_push($times, $m);
  }
  $min = 99999; $max = 0; $sum = 0;
  foreach($times as $t){
    if($t < $min)
      $min = $t;
    if($t > $max)
      $max = $t;
    $sum += $t;
  }
  $avg = $sum / count($times);
  echo "Times are: min=$min, max=$max, avg=$avg\n";
?>
