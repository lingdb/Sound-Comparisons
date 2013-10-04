<?php
  /***/
  function fetchTranslations_Families($dbConnection, $tid, $study, $offset){
    $descriptions = getDescriptions(array('dt_families_trans'), $dbConnection);
    $values = array();
    $q = "SELECT CONCAT(StudyIx, FamilyIx), FamilyNm FROM Families "
       . "LIMIT ".PAGE_ITEM_LIMIT." OFFSET $offset";
    $set = $dbConnection->query($q);
    while($r = $set->fetch_row()){
      $key = $r[0];
      $r = $r[1];
      $entry = array(
        $tid       //'TranslationId'
      , 'Families' //'TableSuffix'
      , $r         //'Family'
      , $key       //'Key'
      , array('Name' => $r)); //'Source'
      $translation = array();
      $q = "SELECT Trans FROM Page_DynamicTranslation_Families "
         . "WHERE CONCAT(StudyIx, FamilyIx) = $key AND TranslationId = $tid";
      if($t = $dbConnection->query($q)->fetch_row()){
        $translation = array('Name' => $t[0]);
      }
      array_push($entry, $translation, $descriptions);
      array_push($values, $entry);
    }
    return $values;
  }
  /***/
  function saveTranslation_Families($dbConnection, $tid, $study, $key, $translation){
    $key = $key[0];
    $q = "DELETE FROM Page_DynamicTranslation_Families "
       . "WHERE CONCAT(StudyIx, FamilyIx) = $key AND TranslationId = $tid";
    $dbConnection->query($q);
    $trans = $translation['Name'];
    $q = "INSERT INTO Page_DynamicTranslation_Families(TranslationId, StudyIx, FamilyIx, Trans) "
       . "SELECT $tid, StudyIx, FamilyIx, '$trans' FROM Families "
       . "WHERE CONCAT(StudyIx, FamilyIx) = $key";
    $dbConnection->query($q);
  }
?>
