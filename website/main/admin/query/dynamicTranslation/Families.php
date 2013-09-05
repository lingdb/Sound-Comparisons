<?php
  /***/
  function fetchTranslations_Families($tid, $study, $offset){
    $descriptions = getDescriptions(array('dt_families_trans'), DB_CONNECTION);
    $values = array();
    $q = "SELECT CONCAT(StudyIx, FamilyIx), FamilyNm FROM Families "
       . "LIMIT ".PAGE_ITEM_LIMIT." OFFSET $offset";
    $set = mysql_query($q, DB_CONNECTION);
    while($r = mysql_fetch_row($set)){
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
      if($t = mysql_fetch_row(mysql_query($q, DB_CONNECTION))){
        $translation = array('Name' => $t[0]);
      }
      array_push($entry, $translation, $descriptions);
      array_push($values, $entry);
    }
    return $values;
  }
  /***/
  function saveTranslation_Families($tid, $study, $key, $translation){
    $key = $key[0];
    $q = "DELETE FROM Page_DynamicTranslation_Families "
       . "WHERE CONCAT(StudyIx, FamilyIx) = $key AND TranslationId = $tid";
    mysql_query($q, DB_CONNECTION);
    $trans = $translation['Name'];
    $q = "INSERT INTO Page_DynamicTranslation_Families(TranslationId, StudyIx, FamilyIx, Trans) "
       . "SELECT $tid, StudyIx, FamilyIx, '$trans' FROM Families "
       . "WHERE CONCAT(StudyIx, FamilyIx) = $key";
    mysql_query($q, DB_CONNECTION);
  }
?>
