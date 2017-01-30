<?php
//Setup:
chdir('..');
require_once('config.php');
require_once('query/dataProvider.php');
/**
  @param $filename String - generated downlaod will be named $filename.csv
  @param $headline String[] - first row of the .csv
  @param $rows     String[][] - second and following rows of the .csv
  Builds a .csv file from a filename, a headline and some rows.
  The file is than presented for download by setting http-headers.
*/
function buildCSV($filename, $headline, $rows){
  //Sanitizing the filename:
  $filename = preg_replace('/[\\?!\\(\\)\\[\\]\\{\\}\\<\\>\\\\\\/]/','',$filename);
  //The final filename will carry the date.
  $filename .= '_'.date('Y-m-d h:i', time()).'.csv';
  ob_start();
  $df = fopen('php://output', 'w');
  //UTF-8 BOM https://en.wikipedia.org/wiki/Byte_order_mark - should help excel a bit.
  fputs($df, chr(239).chr(187).chr(191));
  fputcsv($df, $headline);
  foreach($rows as $row){
    fputcsv($df, $row);
  }
  fclose($df);
  //Headers to initiate download
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: text/csv; charset=utf-16");
  header("Content-Disposition: attachment;filename=\"$filename\"");
  header("Content-Transfer-Encoding: binary");
  ob_end_flush();
}
//Making sure necessary GET parameters exist
if(!array_key_exists('study', $_GET)){
  die('Missing GET parameter: study');
}
if(!array_key_exists('languages', $_GET)){
  die('Missing GET parameter: languages');
}
if(!array_key_exists('words', $_GET)){
  die('Missing GET parameter: words');
}
//Languages to work with:
$languages = array();
foreach(explode(',', $_GET['languages']) as $lIx){
  $languages[$lIx] = true;
}
foreach(DataProvider::getLanguages($_GET['study']) as $l){
  $lIx = $l['LanguageIx'];
  if(array_key_exists($lIx, $languages)){
    $languages[$lIx] = $l;
  }
}
//Words to work with:
$words = array();
foreach(explode(',', $_GET['words']) as $wIx){
  $words[$wIx] = true;
}
foreach(DataProvider::getWords($_GET['study']) as $w){
  $wIx = $w['IxElicitation'].$w['IxMorphologicalInstance'];
  if(array_key_exists($wIx, $words)){
    $words[$wIx] = $w;
  }
}
//Transcriptions to work with:
$transcriptions = array();
foreach(DataProvider::getTranscriptions($_GET['study']) as $t){
  $lIx = $t['LanguageIx'];
  if(array_key_exists($lIx, $languages)){
    $wIx = $t['IxElicitation'].$t['IxMorphologicalInstance'];
    if(array_key_exists($wIx, $words)){
      $tIx = $lIx.'-'.$wIx;
      if(array_key_exists($tIx, $transcriptions)){
        array_push($transcriptions[$tIx], $t);
      }else{
        $transcriptions[$tIx] = array($t);
      }
    }
  }
}
//Building the .csv:
$filename = 'Customexport';
$headline = array("LanguageId", "LanguageName", "Latitude", "Longitude"
                , "WordId", "WordModernName1", "WordModernName2", "WordProtoName1", "WordProtoName2"
                , "Phonetic", "SpellingAltv1", "SpellingAltv2", "NotCognateWithMainWordInThisFamily2");
$rows = array();
foreach($languages as $lIx => $l){
  foreach($words as $wIx => $w){
    $key = $lIx.'-'.$wIx;
    if(array_key_exists($key, $transcriptions)){
      foreach($transcriptions[$key] as $t){
        array_push($rows, array(
          $lIx, $l['ShortName'], $l['Latitude'], $l['Longtitude']
        , $wIx, $w['FileNameRfcModernLg01'], $w['FileNameRfcModernLg02'], $w['FileNameRfcProtoLg01'], $w['FileNameRfcProtoLg02']
        , $t['Phonetic'], $t['SpellingAltv1'], $t['SpellingAltv2'], $t['NotCognateWithMainWordInThisFamily']
        ));
      }
    }
  }
}
buildCSV($filename, $headline, $rows);
