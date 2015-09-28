<?php
  /**
    This script creates a dump of all translations in the database as a JSON object.
    Having a JSON object rather than a SQL script allows us, to merge translations
    in a clever fashion instead of simply replacing all of them.
    The dynamic translations, for example, have a timestamp attached,
    so that we can keep the latest of them even when they differ between machines.
  */
  /* Setup and session verification */
  chdir('..');
  require_once('common.php');
  //We only check for the session, if not on cli:
  if(php_sapi_name() !== 'cli'){
    session_validate()     or Config::error('403 Forbidden');
    session_mayTranslate() or Config::error('403 Forbidden');
  }
  //Our information object:
  $info = array(
    'dynamicTranslation' => array() // Hash -> Page_DynamicTranslation
  , 'staticDescription'  => array() // Req -> Description
  , 'staticTranslation'  => array() // TranslationId -> [{Req, Trans, IsHtml}]
  , 'translations'       => array() // TranslationId -> Page_Translation\{TranslationId}
  );
  //Fetching translations:
  $q = 'SELECT TranslationId, TranslationName, BrowserMatch, ImagePath, Active, RfcLanguage, '
     . 'UNIX_TIMESTAMP(lastChangeStatic) AS \'lastChangeStatic\', '
     . 'UNIX_TIMESTAMP(lastChangeDynamic) AS \'lastChangeDynamic\' '
     . 'FROM Page_Translations';
  $set = $dbConnection->query($q);
  while($r = $set->fetch_assoc()){
    $tId = $r['TranslationId'];
    unset($r['TranslationId']);
    $info['translations'][$tId] = $r;
  }
  //Cast to object to aid json_encode:
  $info['translations'] = (object)$info['translations'];
  //Fetching static descriptions:
  $q = 'SELECT Req, Description FROM Page_StaticDescription';
  $set = $dbConnection->query($q);
  while($r = $set->fetch_assoc()){
    $info['staticDescription'][$r['Req']] = $r['Description'];
  }
  //Fetching static translations:
  $q = 'SELECT TranslationId, Req, Trans, IsHtml FROM Page_StaticTranslation';
  $set = $dbConnection->query($q);
  while($r = $set->fetch_assoc()){
    $tId = $r['TranslationId'];
    if(!array_key_exists($tId, $info['staticTranslation'])){
      $info['staticTranslation'][$tId] = array();
    }
    array_push($info['staticTranslation'][$tId], array(
      'Req'    => $r['Req']
    , 'Trans'  => $r['Trans']
    , 'IsHtml' => $r['IsHtml']
    ));
  }
  //Cast to object to aid json_encode:
  $info['staticTranslation'] = (object)$info['staticTranslation'];
  //Fetching dynamic translations:
  $q = 'SELECT MD5(CONCAT(TranslationId, Category, Field)) AS \'Hash\', '
     . 'TranslationId, Category, Field, Trans, '
     . 'UNIX_TIMESTAMP(Time) AS \'Time\' '
     . 'FROM Page_DynamicTranslation';
  $set = $dbConnection->query($q);
  while($r = $set->fetch_assoc()){
    $hash = $r['Hash'];
    unset($r['Hash']);
    $info['dynamicTranslation'][$hash] = $r;
  }
  //Delivering $info:
  Config::setResponseJSON();
  $filename = 'translations_'.date('Y-m-d-h:i', time()).'.json';
  header('Content-Disposition: attachment;filename="'.$filename.'"');
  echo Config::toJSON($info, $opts);
