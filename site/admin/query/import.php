<?php
  /**
    This script processes a dump of translations in JSON format, as generated by export.php.
    The dump can either be supplied as a command line argument, or be given in a POST request.
    To process the JSON, we compare it to a current local export.
    For both, static and dynamic translations, we keep the values that where set last.
  */
  /* Setup and session verification */
  chdir('..');
  require_once('common.php');
  chdir('query');//Need to go back for current export.
  //We only check for the session, if not on cli:
  if(php_sapi_name() !== 'cli'){
    session_validate()     or Config::error('403 Forbidden');
    session_mayTranslate() or Config::error('403 Forbidden');
    //Reading import from upload:
    $file   = file_get_contents($_FILES['import']['tmp_name']);
    $import = json_decode($file, true);
    unset($file);
  }else{
    //On cli we expect the first argument to be the import file:
    if(count($argv) <= 1){
      die('Please supply the import .json file as a parameter.');
    }
    //Reading import from argv:
    $file   = file_get_contents($argv[1]);
    $import = json_decode($file, true);
    unset($file);
  }
  //Getting the current export to compare against it:
  $current = json_decode(`php -f export.php`, true);
  //Merging our data:
  $merged = array(
    'dynamicTranslation' => array() // Hash -> Page_DynamicTranslation
  , 'staticDescription'  => array_replace($current['staticDescription'], $import['staticDescription'])
  , 'staticTranslation'  => array() // TranslationId -> [{Req, Trans, IsHtml}]
  , 'translations'       => array_replace($current['translations'], $import['translations'])
  );
  //Merging static translations:
  foreach(array_keys($merged['translations']) as $tId){
    $keep = null; $cT = null; $iT = null;
    //Do we have a current static translation for this tId?
    if(array_key_exists($tId, $current['staticTranslation'])){
      $cT = $current['staticTranslation'][$tId];
      $keep = $cT;
    }
    //Do we have an import static translation for this tId?
    if(array_key_exists($tId, $import['staticTranslation'])){
      $iT = $import['staticTranslation'][$tId];
      $keep = $iT;
    }
    //Compare them, iff we have both:
    if($cT && $iT){
      $c = $current['translations'][$tId]['lastChangeStatic'];
      $i =  $import['translations'][$tId]['lastChangeStatic'];
      $keep = ($c > $i) ? $cT : $iT;
    }
    //If we've got at least one, we take the one that keep decides:
    if($keep){
      $merged['staticTranslation'][$tId] = $keep;
    }
  }
  //Merging dynamic translations:
  $hashes = array_unique(
    array_keys($current['dynamicTranslation'])
  + array_keys( $import['dynamicTranslation'])
  );
  foreach($hashes as $h){
    $keep = null; $c = null; $i = null;
    if(array_key_exists($h, $current['dynamicTranslation'])){
      $c = $current['dynamicTranslation'][$h];
      $keep = $c;
    }
    if(array_key_exists($h, $import['dynamicTranslation'])){
      $i = $import['dynamicTranslation'][$h];
      $keep = $i;
    }
    if($c && $i){
      $keep = ($c['Time'] > $i['Time']) ? $c : $i;
    }
    if($keep){
      $merged['dynamicTranslation'][$h] = $keep;
    }
  }
  //Done merging, generating SQL:
  unset($current, $import);
  //Helper functions:
  $compose = function($xs){
    foreach($xs as $i => $x)
      $xs[$i] = '('.implode(',', $x).')';
    return implode(',', $xs);
  };
  $esc = function($s) use ($dbConnection){
    return $dbConnection->escape_string($s);
  };
  $wrap = function($s) use ($dbConnection){
    return "'".$dbConnection->escape_string($s)."'";
  };
  //Initial statements:
  $sql = array(
    'SET AUTOCOMMIT=0'
  , 'SET FOREIGN_KEY_CHECKS=0'
  , 'DELETE FROM Page_StaticTranslation'
  , 'DELETE FROM Page_DynamicTranslation'
  , 'DELETE FROM Page_StaticDescription'
  , 'DELETE FROM Page_Translations'
  );
  //Insert for Page_Translations:
  $xs = array();
  foreach($merged['translations'] as $tId => $t){
    array_push($xs, array(
      $tId
    , $wrap($t['TranslationName'])
    , $wrap($t['BrowserMatch'])
    , $wrap($t['ImagePath'])
    , $esc($t['Active'])
    , $t['RfcLanguage'] ? $esc($t['RfcLanguage']) : 'NULL'
    , 'FROM_UNIXTIME('.$esc($t['lastChangeStatic']).')'
    , 'FROM_UNIXTIME('.$esc($t['lastChangeDynamic']).')'
    ));
  }
  array_push($sql,
    'INSERT INTO Page_Translations ('
  . 'TranslationId, TranslationName, BrowserMatch, ImagePath, '
  . 'Active, RfcLanguage, lastChangeStatic, lastChangeDynamic) '
  . 'VALUES '.$compose($xs));
  //Insert for Page_StaticDescription:
  $xs = array();
  foreach($merged['staticDescription'] as $req => $desc){
    array_push($xs, array($wrap($req), $wrap($desc)));
  }
  array_push($sql,
    'INSERT INTO Page_StaticDescription (Req, Description) '
  . 'VALUES '.$compose($xs));
  //Insert for Page_StaticTranslation:
  $xs = array();
  foreach($merged['staticTranslation'] as $tId => $ts){
    $id = $esc($tId);
    foreach($ts as $t){
      array_push($xs, array(
        $id
      , $wrap($t['Req'])
      , $wrap($t['Trans'])
      , $esc($t['IsHtml'])
      ));
    }
  }
  array_push($sql,
    'INSERT INTO Page_StaticTranslation (TranslationId, Req, Trans, IsHtml) '
  . 'VALUES'.$compose($xs));
  //Insert for Page_DynamicTranslation:
  $xs = array();
  foreach($merged['dynamicTranslation'] as $dt){
    array_push($xs, array(
      $esc($dt['TranslationId'])
    , $wrap($dt['Category'])
    , $wrap($dt['Field'])
    , $wrap($dt['Trans'])
    , 'FROM_UNIXTIME('.$esc($dt['Time']).')'
    ));
  }
  array_push($sql,
    'INSERT INTO Page_DynamicTranslation (TranslationId, Category, Field, Trans, Time) '
  . 'VALUES '.$compose($xs)
  );
  //Final statements:
  array_push($sql,
    'SET FOREIGN_KEY_CHECKS=1'
  , 'COMMIT'
  , 'SET AUTOCOMMIT=1'
  );
  //Finishing:
  if(php_sapi_name() !== 'cli'){
    $dbConnection->multi_query(implode(';', $sql));
    header('LOCATION: ../index.php');
  }else{ // On cli we only output the script.
    echo implode(";\n", $sql);
  }
