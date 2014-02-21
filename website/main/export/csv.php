<?php
//Initial setup:
if(!isset($valueManager)){
  chdir('..');
  require_once 'config.php';
  require_once 'valueManager/RedirectingValueManager.php';
  $dbConnection = Config::getConnection();
  $valueManager = RedirectingValueManager::getInstance();
}
/**
  @param $filename String - generated downlaod will be named $filename.csv
  @param $headline String[] - first row of the .csv
  @param $rows     String[][] - second and following rows of the .csv
  Builds a .csv file from a filename, a headline and some rows.
  The file is than presented for download by setting http-headers.
*/
function buildCSV($filename, $headline, $rows){
  $filename .= '_'.date('Y-m-d h:i', time()).'.csv';
  ob_start();
  $df = fopen('php://output', 'w');
  fputcsv($df, $headline);
  foreach($rows as $row){
    fputcsv($df, $row);
  }
  fclose($df);
  //Headers to initiate download
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Type: application/octet-stream");
  header("Content-Type: application/download");
  header("Content-Disposition: attachment;filename={$filename}");
  header("Content-Transfer-Encoding: binary");
  ob_end_flush();
}
/***/
function wordHeadline(){
  return array("WordName","WordId","WordModernName","WordProtoName");
}
/***/
function wordRow($w){
  $wName = $w->getKey();
  $wId   = $w->getId();
  $wMN   = $w->getModernName();
  $wPr   = $w->getProtoName();
  return array($wName, $wId, $wMN, $wPr);
}
/***/
function languageHeadline(){
  return array("LanguageName","LanguageId", "Latitude", "Longtitude");
}
/***/
function languageRow($l){
  $lName = $l->getShortName(false);
  $lId   = $l->getId();
  $loc   = $l->getLocation();
  $lat   = $loc ? $loc[0] : '';
  $lng   = $loc ? $loc[1] : '';
  return array($lName, $lId, $lat, $lng);
}
/***/
function transcriptionHeadline(){
  return array("AltSpelling"
    ,"PhoneticTranscription1","NotCognateWithMainWordInThisFamily1"
    ,"PhoneticTranscription2","NotCognateWithMainWordInThisFamily2");
}
/***/
function transcriptionRow($tr){
  $phonetics = $tr->getTranscriptions();
  $nCognates = $tr->getNotCognates();
  $altSpelling = ($s = $tr->getAltSpelling()) ? $s : '';
  $p0  = (count($phonetics) >= 1) ? $phonetics[0] : '';
  $p1  = (count($phonetics) >= 2) ? $phonetics[1] : '';
  $nc0 = (count($nCognates) >= 1) ? $nCognates[0] : false;
  $nc1 = (count($nCognates) >= 2) ? $nCognates[1] : false;
  return array($altSpelling, $p0, $nc0, $p1, $nc1);
}
//Acting according to the valueManager:
$v = $valueManager;
//Building the .csv:
$filename = 'undefined_export';
$headline = array();
$rows = array();
if($v->gpv()->isView('WordView')||$v->gpv()->isView('MapView')){
  $word = current($v->getWords());
  $filename = "Wordexport_".$word->getKey();
  $headline = array_merge(array("FamilyName","RegionName","RegionId")
     , languageHeadline()
     , transcriptionHeadline());
  foreach($v->getStudy()->getFamilies() as $f){
    $fName = $f->getName();
    foreach($f->getRegions() as $r){
      $rName = $r->getName();
      $rId   = $r->getId();
      foreach($r->getLanguages() as $l){
        $lr  = languageRow($l);
        $tr  = transcriptionRow(Transcription::getTranscriptionForWordLang($word, $l));
        $row = array_merge(array($fName, $rName, $rId), $lr, $tr);
        array_push($rows, $row);
      }
    }
  }
}else if($v->gpv()->isView('LanguageView')){
  $language = current($v->getLanguages());
  $filename = "Languagexport_".$language->getShortName(false);
  $headline = array_merge(wordHeadline(), transcriptionHeadline());
  foreach($v->getStudy()->getWords() as $w){
    $wr = wordRow($w);
    $tr = transcriptionRow(Transcription::getTranscriptionForWordLang($w, $language));
    array_push($rows, array_merge($wr, $tr));
  }
}else if($v->gpv()->isSelection()){
  $filename = "Customexport";
  $headline = array_merge(languageHeadline(), wordHeadline(), transcriptionHeadline());
  $ls = $v->getLanguages();
  $ws = $v->getWords();
  if(count($ls) === 0) $ls = $v->getStudy()->getLanguages();
  if(count($ws) === 0) $ws = $v->getStudy()->getWords();
  foreach($ls as $l){
    $lr = languageRow($l);
    foreach($ws as $w){
      $wr = wordRow($w);
      $tr = transcriptionRow(Transcription::getTranscriptionForWordLang($w, $l));
      array_push($rows, array_merge($lr, $wr, $tr));
    }
  }
}
buildCSV($filename, $headline, $rows);
?>
