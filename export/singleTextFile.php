<?php
/**
  This script provides single phonetic transcriptions as downloads in textfiles.
*/
//Checking if all expected parameters are given:
$params = array('word','language','study','n',);
foreach($params as $p){
  if(!array_key_exists($p, $_GET)){
    die('The following parameters must be supplied: '.implode(', ', $params));
  }
}
//Setup:
chdir('..');
require_once 'config.php';
require_once 'valueManager/RedirectingValueManager.php';
$db = Config::getConnection();
//Values to work with:
$w = $db->escape_string($_GET['word']);
$l = $db->escape_string($_GET['language']);
$s = $db->escape_string($_GET['study']);
$n = preg_match('/^\d+$/', $_GET['n']) ? $_GET['n'] : 0;
$v = RedirectingValuemanager::getInstance();
//Database objects:
$s = new StudyFromKey($v, $s);
$v = $v->gsm()->setStudy($s);
$w = new WordFromId($v, $w, $s);
$l = new LanguageFromId($v, $l);
$t = Transcription::getTranscriptionForWordLang($w, $l);
//The transcriptions:
$ts = $t->getTranscriptions();
if($n >= count($ts)){
  die('Sorry, cannot deliver n='.$n.' for values: '.implode(', ', $ts));
}
//Figuring out the filename:
$sfs  = $t->getSoundFiles(array('.mp3'));
$file = preg_replace('/mp3$/', 'txt', basename(current($sfs[$n])));
//Delivering the content:
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment;filename="'.$file.'"');
echo $ts[$n];
?>
