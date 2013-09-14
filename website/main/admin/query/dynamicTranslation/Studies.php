<?php
  /***/
  function fetchTranslations_Studies($tid, $study, $offset){
    $descriptions = getDescriptions(array('dt_studies_trans'), DB_CONNECTION);
    $values = array();
    $q = "SELECT DISTINCT Name FROM Studies LIMIT ".PAGE_ITEM_LIMIT." OFFSET $offset";
    $set = DB_CONNECTION->query($q);
    while($r = $set->fetch_row()){
      $r = $r[0];
      $entry = array(
        $tid                  //'TranslationId'
      , 'Studies'             //'TableSuffix'
      , $r                    //'Study'
      , $r                    //'Key'
      , array('Name' => $r)); //'Source'
      $translation = array();
      $q = "SELECT Trans FROM Page_DynamicTranslation_Studies WHERE TranslationId = $tid AND Study = '$r'";
      if($t = DB_CONNECTION->query($q)->fetch_row()){
        $translation = array('Name' => $t[0]);
      }
      array_push($entry, $translation, $descriptions);
      array_push($values, $entry);
    }
    return $values;
  }
  /***/
  function saveTranslation_Studies($tid, $study, $key, $translation){
    $key = $key[0];
    $q = "DELETE FROM Page_DynamicTranslation_Studies WHERE TranslationId = $tid AND Study = '$key'";
    DB_CONNECTION->query($q);
    $trans = $translation['Name'];
    $q = "INSERT INTO Page_DynamicTranslation_Studies(TranslationId, Study, Trans) "
       . "VALUES ($tid, '$key', '$trans')";
    DB_CONNECTION->query($q);
  }
?>
