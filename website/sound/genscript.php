<?php
/**
  This script crawls ./ for all files that end with .ogg or .mp3.
  It than attempts to use avconv to generate .ogg from .mp3 or .mp3 from .ogg
  if one of them is missing.
*/
$find  = shell_exec("find . -type f \( -name '*.ogg' -o -name '*.mp3' -o -name '*.wav' \)");
$files = explode("\n", $find);
$table = array();
foreach($files as $f){
  preg_match('/(.*)(ogg|mp3|wav)$/', $f, $matches);
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
  $hasWav = in_array('wav', $suffixes);
  $hasOgg = in_array('ogg', $suffixes);
  $hasMp3 = in_array('mp3', $suffixes);
  if(!$hasOgg){
    $ext = $hasWav ? 'wav' : 'mp3';
    echo "avconv -y -i $name$ext -acodec libvorbis -aq 60 $name"."ogg\n";
  }
  if(!$hasMp3){
    $ext = $hasWav ? 'wav' : 'ogg';
    echo "avconv -y -i $name$ext -acodec libmp3lame -aq 60 $name"."mp3\n";
  }
//exec($command);
}
?>
