<?php
  /**
    This script reads exported translations in JSON syntax,
    and extracts filters the given translationIds.
    The resulting data is echoed as JSON.
  */
  if(count($argv) <= 2){
    $name = $argv[0];
    echo "Usage: $name file translationId …\n";
  }else{
    //Set of ids to pick:
    $pick = array();
    for($i = 2; $i < count($argv); $i++){
      $pick[$argv[$i]] = true;
    }
    $chk = function($tId) use ($pick){
      if(array_key_exists($tId, $pick))
        return $pick[$tId];
      return false;
    };
    //Data to work on:
    $read = json_decode(file_get_contents($argv[1]), true);
    $data = array();
    //Always copy descriptions:
    if(array_key_exists('staticDescription', $read)){
      $data['staticDescription'] = $read['staticDescription'];
    }
    //Filtering translations:
    if(array_key_exists('translations', $read)){
      $data['translations'] = array();
      foreach($read['translations'] as $tId => $tData){
        if($chk($tId)){
          $data['translations'][$tId] = $tData;
        }
      }
    }
    //Filtering static translations:
    if(array_key_exists('staticTranslation', $read)){
      $data['staticTranslation'] = array();
      foreach($read['staticTranslation'] as $tId => $tData){
        if($chk($tId)){
          $data['staticTranslation'][$tId] = $tData;
        }
      }
    }
    //Filtering dynamic translations:
    if(array_key_exists('dynamicTranslation', $read)){
      $data['dynamicTranslation'] = array();
      foreach($read['dynamicTranslation'] as $hash => $tData){
        $tId = $tData['TranslationId'];
        if($chk($tId)){
          $data['dynamicTranslation'][$hash] = $tData;
        }
      }
    }
    //Generating output:
    $opts = 0;
    if(defined('JSON_PRETTY_PRINT'))      $opts |= JSON_PRETTY_PRINT;
    if(defined('JSON_UNESCAPED_UNICODE')) $opts |= JSON_UNESCAPED_UNICODE;
    if(defined('JSON_NUMERIC_CHECK'))     $opts |= JSON_NUMERIC_CHECK;
    echo json_encode($data, $opts);
  }
?>
