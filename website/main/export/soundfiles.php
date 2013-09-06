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
  require_once 'valueManager/RedirectingValueManager.php';
  $dbConnection = $config->getConnection();
  $valueManager = new RedirectingValueManager($dbConnection, $config);
}
$v = $valueManager;
$path = $config->getDownloadPath();
//0.: Deleting files older than one hour
$q = "SELECT FileName FROM Export_Soundfiles WHERE "
   . "Creation <= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 HOUR)";
$set = mysql_query($q, $dbConnection);
while($r = mysql_fetch_row($set)){
  $fName = $r[0];
  $target = "$path/$fName";
  if(is_readable($target)){
    if(unlink($target)){
      mysql_query("DELETE FROM Export_Soundfiles WHERE FileName = '$fName'", $dbConnection);
    }
  }
}
//1.: Finding a name for the .zip to download
do{
  $time  = time();
  $rand  = substr(str_shuffle('0123456789ABCDEF'), 0, 5);
  $fName = "export-$time-$rand.zip";
  $q = "INSERT INTO Export_Soundfiles(FileName) VALUES ('$fName')";
  mysql_query($q, $dbConnection);
  if(mysql_errno($dbConnection))
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
    $t = new TranscriptionFromWordLang($word, $language);
    //2.1.: Adding the SoundFiles from the Transcriptions to the .zip
    //We only add .mp3 because of smaller filesize, and compression not finishing otherwise.
    foreach($t->getSoundFiles(array('.mp3')) as $sf){
      foreach($sf as $f)
        $zip->addFile($f, preg_replace('/\.\./', '', $f));
    }
  }
}
//3.: Forwarding the client to the soundfile
$zip->close();
header("LOCATION: ../$target");
?>
