<?php
  chdir('..');
  $info  = array();
  $sums  = `md5sum templates/*.html`;
  $lines = explode("\n", $sums);
  foreach($lines as $l){
    $x = explode('  ', $l);
    if(count($x) !== 2)
      continue;
    $info[$x[1]] = $x[0];
  }
  echo json_encode($info);
?>
