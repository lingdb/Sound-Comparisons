<?php
  chdir('..');
  $info  = array();
  $sums  = `md5sum templates/*.html`;
  $lines = explode("\n", $sums);
  foreach($lines as $l){
    $x = explode('  ', $l);
    if(count($x) !== 2)
      continue;
    $info[$x[0]] = $x[1];
  }
  echo json_encode($info);
?>
