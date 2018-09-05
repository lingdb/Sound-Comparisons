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
  date_default_timezone_set('Europe/Berlin');
  $filename .= '_'.date('Y-m-d H:i', time()).'.csv';
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
/**
  @param $filename String - generated downlaod will be named $filename.tsv
  @param $headline String[] - first row of the .tsv
  @param $rows     String[][] - second and following rows of the .tsv
  Builds a .tsv file from a filename, a headline and some rows.
  The file is than presented for download by setting http-headers.
*/
function buildTSV($filename, $headline, $rows){
  //Sanitizing the filename:
  $filename = preg_replace('/[\\?!\\(\\)\\[\\]\\{\\}\\<\\>\\\\\\/]/','',$filename);
  //The final filename will carry the date.
  date_default_timezone_set('Europe/Berlin');
  $filename .= '_'.date('Y-m-d H:i', time()).'.tsv';
  ob_start();
  $df = fopen('php://output', 'w');
  //UTF-8 BOM https://en.wikipedia.org/wiki/Byte_order_mark - should help excel a bit.
  fputs($df, chr(239).chr(187).chr(191));
  fwrite($df, join("\t", $headline)."\n");
  foreach($rows as $row){
    fwrite($df, join("\t", $row)."\n");
  }
  fclose($df);
  //Headers to initiate download
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: text/tab-separated-values; charset=utf-16");
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
$template = array();
$edictor = false;
if(array_key_exists('tsv', $_GET)){
  $edictor = true;
  $headline = array('ID','LG_ID','DOCULECT','LG_FILEPATHPART','GLOTTOCODE',
  'LONGITUDE','LATITUDE','STUDYIX','FAMILYIX','COGID','IXMORPHINSTANCE',
  'CONCEPT','CONCEPTICON_ID','IPA','TOKENS','ALIGNMENT','SPELLINGALTV1');
}else{
  $headline = array("LanguageId", "LanguageName", "Latitude", "Longitude"
                , "WordId", "WordModernName1", "WordModernName2", "WordProtoName1", "WordProtoName2"
                , "Phonetic", "SpellingAltv1", "SpellingAltv2", "NotCognateWithMainWordInThisFamily2");
}
$rows = array();
$cnt = 0;
foreach($languages as $lIx => $l){
  foreach($words as $wIx => $w){
    $key = $lIx.'-'.$wIx;
    if(array_key_exists($key, $transcriptions)){
      foreach($transcriptions[$key] as $t){
        if($edictor){
          $cnt = $cnt + 1;
          array_push($rows, array(
            $cnt,
            $lIx, $l['ShortName'],
            $l['FilePathPart'], $l['GlottoCode'],
            $l['Longtitude'], $l['Latitude'],
            $l['StudyIx'], $l['FamilyIx'],1,$t['IxMorphologicalInstance'],$w['FullRfcModernLg01'],0,
            trim($t['Phonetic']),"",0,$t['SpellingAltv1']
          ));
        }else{
          array_push($rows, array(
            $lIx, $l['ShortName'], $l['Latitude'], $l['Longtitude']
          , $wIx, $w['FileNameRfcModernLg01'], $w['FileNameRfcModernLg02'], $w['FileNameRfcProtoLg01'], $w['FileNameRfcProtoLg02']
          , $t['Phonetic'], $t['SpellingAltv1'], $t['SpellingAltv2'], $t['NotCognateWithMainWordInThisFamily']
          ));
        }
      }
    }
  }
}
if($edictor){
  buildTSV($filename, $headline, $rows);
}else{
  buildCSV($filename, $headline, $rows);
}
