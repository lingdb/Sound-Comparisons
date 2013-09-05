<?php
/**
  This script crawls ./ for all files that end with .ogg or .mp3.
  It than attempts to use avconv to generate .ogg from .mp3 or .mp3 from .ogg
  if one of them is missing.
*/
$find  = shell_exec("find . -type f \( -name '*.ogg' -o -name '*.mp3' \)");
$files = explode("\n", $find);
$table = array();
foreach($files as $f){
  preg_match('/(.*)(ogg|mp3)$/', $f, $matches);
  if(count($matches) < 3) continue;
  $name   = $matches[1];
  $suffix = $matches[2];
  if(!$name) continue;
  if(!array_key_exists($name, $table)){
    $table[$name] = array($suffix);
  }else{
    array_push($table[$name],$suffix);
  }
}
foreach($table as $name => $suffixes){
  if(count($suffixes) !== 1) continue;
  switch($suffixes[0]){
    case 'mp3':
      $command = "avconv -y -i $name"."mp3 -acodec libvorbis -aq 60 $name"."ogg";
    break;
    case 'ogg':
      $command = "avconv -y -i $name"."ogg -acodec libmp3lame -aq 60 $name"."mp3";
    break;
  }
  echo "$command\n";
//exec($command);
}
?>
