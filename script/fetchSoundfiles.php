<?php
/**
  This script fetches all sound files from the target server,
  and stores them in the sound directory.
*/
$server = 'http://soundcomparisons.com/';
//Switching into website directory:
chdir(__DIR__);
chdir('../site');
//Global data to iterate studies:
echo "Fetching global data form $server\n";
$globalUrl = $server.'query/data?global';
$global = json_decode(file_get_contents($globalUrl), true);
//Fetching studies to retrieve file list:
$files = array();
$addFiles = function($fs) use (&$files, &$addFiles) {
  if(is_array($fs)){
    foreach($fs as $f){
      $addFiles($f);
    }
  }else{
    array_push($files, $fs);
  }
};
echo "Iterating studies\n";
foreach($global['studies'] as $studyName){
  echo "Fetching data for $studyName\n";
  $study = json_decode(file_get_contents($server.'query/data?study='.$studyName), true);
  echo "Iterating transcriptions\n";
  foreach($study['transcriptions'] as $transcription){
    $addFiles($transcription['soundPaths']);
  }
}
//Filtering existing sound files:
$missing = array();
echo "Script knows about ".count($files)." sound files,\nfiltering existing ones\n";
foreach($files as $f){
  if(file_exists($f))
    continue;
  array_push($missing, $f);
}
echo "Need to download ".count($missing)." files from server.\n";
//Create missing directories:
$dirs = array();
foreach($missing as $f){
  $parts = explode('/', $f);
  array_pop($parts);
  $dir = implode('/', $parts);
  $dirs[$dir] = true;
}
$dirs = array_keys($dirs);
echo "Creating missing directories\n";
foreach($dirs as $dir){
  echo "$dir\n";
  `mkdir -p $dir`;
}
//Downloading files:
echo "Downloading files\n";
foreach($missing as $f){
  echo "$f\n";
  `wget -O $f $server$f`;
}
