<?php
/*
  This process works in several steps:
  0.: Deleting files older than one hour
  1.: Finding the soundfiles for the given tIds
  2.: Finding a name for the .zip to download
  2.1.: Adding the Soundfiles described by $_GET['files'] to the .zip
  3.: Forwarding the client to the soundfile
*/
//Setup:
chdir('..');
require_once 'config.php';
require_once 'query/dataProvider.php';
$dbConnection = Config::getConnection();
$path = Config::$downloadPath;
//0.: Deleting files older than one hour
$q = "SELECT FileName FROM Export_Soundfiles WHERE "
   . "Creation <= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 HOUR)";
$set = $dbConnection->query($q);
while($r = $set->fetch_row()){
  $fName = $r[0];
  $target = "$path/$fName";
  if(is_readable($target)){
    if(unlink($target)){
      $dbConnection->query("DELETE FROM Export_Soundfiles WHERE FileName = '$fName'");
    }
  }
}
//1.: Finding the soundfiles for the given tIds:
if(!isset($_GET['files'])){
  die('Missing required files paramter.');
}
if(!isset($_GET['study'])){
  die('Missing required study paramter.');
}
if(!isset($_GET['suffix'])){
  die('Missing required suffix paramter.');
}
$tIds  = explode(';', $_GET['files']);
$tData = DataProvider::getTranscriptions($_GET['study']);
$files = array();
$sfx   = $_GET['suffix'];
$sfxl  = strlen($sfx);
foreach($tIds as $tId){
  if(array_key_exists($tId, $tData)){
    $t = $tData[$tId];
    if(array_key_exists('soundPaths', $t)){
      foreach($t['soundPaths'] as $fp){
        if($sfx === '' || substr($fp, -$sfxl) === $sfx){
          array_push($files, $fp);
        }
      }
    }
  }
}
//2.: Finding a name for the .zip to download
do{
  $time  = time();
  $rand  = substr(str_shuffle('0123456789ABCDEF'), 0, 5);
  $fName = "export-$time-$rand.zip";
  $q = "INSERT INTO Export_Soundfiles(FileName) VALUES ('$fName')";
  $dbConnection->query($q);
  if($dbConnection->errno)
    $fName = '';
}while($fName === '');
$zip    = new ZipArchive();
$target = "$path/$fName";
$zip->open($target, ZipArchive::CREATE);
//2.1.: Adding Soundfiles:
$added = 0;
foreach($files as $f){
  if(file_exists($f)){
    $zip->addFile($f, basename($f));
    $added++;
  }else{
    error_log('Soundfile not found: '.$f);
  }
}
//3.: Forwarding the client to the soundfile
$zip->close();
if($added > 0){
  header("LOCATION: ../$target");
}else{
  die('Due to internal errors, no soundfiles have been found. Sorry.');
}
?>
