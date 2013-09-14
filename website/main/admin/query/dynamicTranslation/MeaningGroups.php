<?php
  /***/
  function fetchTranslations_MeaningGroups($tid, $study, $offset){
    $descriptions = getDescriptions(array('dt_meaningGroups_trans'), DB_CONNECTION);
    $values = array();
    $q = "SELECT MeaningGroupIx, Name FROM MeaningGroups LIMIT ".PAGE_ITEM_LIMIT." OFFSET $offset";
    $set = DB_CONNECTION->query($q);
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
      if($r = DB_CONNECTION->query($q)->fetch_row()){
        $translation = array('Name' => $r[0]);
      }
      array_push($entry, $translation, $descriptions);
      //Done
      array_push($values, $entry);
    }
    return $values;
  }
  /***/
  function saveTranslation_MeaningGroups($tid, $study, $key, $translation){
    $key = $key[0];
    $q = "DELETE FROM Page_DynamicTranslation_MeaningGroups "
       . "WHERE TranslationId = $tid AND MeaningGroupIx = $key";
    DB_CONNECTION->query($q);
    $translation = $translation['Name'];
    $q = "INSERT INTO Page_DynamicTranslation_MeaningGroups(TranslationId, MeaningGroupIx, Trans) "
       . "VALUES ($tid, $key, '$translation')";
    DB_CONNECTION->query($q);
  }
?>
