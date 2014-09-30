<?php
/*
  This process works in several steps:
  0.: Deleting files older than one hour
  1.: Finding a name for the .zip to download
  2.: Iterating all Tuples of (Word,Language) to make Transcriptions from it
  2.1.: Adding the SoundFiles from the Transcriptions to the .zip
  3.: Forwarding the client to the soundfile
*/
//Making sure we have a valueManager:
if(!isset($valueManager)){
  chdir('..');
  require_once 'config.php';
  require_once 'stopwatch.php';
  require_once 'valueManager/RedirectingValueManager.php';
  $dbConnection = Config::getConnection();
  $valueManager = RedirectingValueManager::getInstance();
}
$v = $valueManager;
$path = Config::$downloadPath;
//Dealing with filetypes:
$formats = array('.mp3');
if(isset($_GET['format']) && preg_match('/^[[:alnum:]]{3,4}$/', $_GET['format'])){
  $formats = array('.'.$_GET['format']);
}
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
//1.: Finding a name for the .zip to download
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
//2.: Iterating all Tuples of (Word,Language) to make Transcriptions from it
$v->gpv();//Calling to PageViewManager to aid loading defaults.
$languages = $v->getLanguages();
$words = $v->getWords();
if(count($languages) === 0)
  $languages = $v->getStudy()->getLanguages();
if(count($words) === 0)
  $words = $v->getStudy()->getWords();
foreach($languages as $language){
  foreach($words as $word){
    $t = Transcription::getTranscriptionForWordLang($word, $language);
    //2.1.: Adding the SoundFiles from the Transcriptions to the .zip
    //We only add .mp3 because of smaller filesize, and compression not finishing otherwise.
    foreach($t->getSoundFiles($formats) as $sf){
      foreach($sf as $f)
        $zip->addFile($f, basename($f));
    }
  }
}
//3.: Forwarding the client to the soundfile
$zip->close();
header("LOCATION: ../$target");
?>