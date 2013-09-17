<?php
  /***/
  function fetchTranslations_MeaningGroups($dbConnection, $tid, $study, $offset){
    $descriptions = getDescriptions(array('dt_meaningGroups_trans'), $dbConnection);
    $values = array();
    $q = "SELECT MeaningGroupIx, Name FROM MeaningGroups LIMIT ".PAGE_ITEM_LIMIT." OFFSET $offset";
    $set = $dbConnection->query($q);
    while($r = $set->fetch_row()){
      $key = $r[0];
      //The basic entry:
      $entry = array(
        $tid                   //'TranslationId'
      , 'MeaningGroups'        //'TableSuffix'
      , $study                 //'Study'
      , $key                   //'Key'
      , array('Name' => $r[1]) //'Source'
      );
      //Existing translations:
      $translation = array();
      $q = "SELECT Trans FROM Page_DynamicTranslation_MeaningGroups "
         . "WHERE TranslationId = $tid AND MeaningGroupIx = $key";
      if($r = $dbConnection->query($q)->fetch_row()){
        $translation = array('Name' => $r[0]);
      }
      array_push($entry, $translation, $descriptions);
      //Done
      array_push($values, $entry);
    }
    return $values;
  }
  /***/
  function saveTranslation_MeaningGroups($dbConnection, $tid, $study, $key, $translation){
    $key = $key[0];
    $q = "DELETE FROM Page_DynamicTranslation_MeaningGroups "
       . "WHERE TranslationId = $tid AND MeaningGroupIx = $key";
    $dbConnection->query($q);
    $translation = $translation['Name'];
    $q = "INSERT INTO Page_DynamicTranslation_MeaningGroups(TranslationId, MeaningGroupIx, Trans) "
       . "VALUES ($tid, $key, '$translation')";
    $dbConnection->query($q);
  }
?>
