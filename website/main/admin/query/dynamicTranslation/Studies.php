<?php
  /***/
  function fetchTranslations_Studies($tid, $study, $offset){
    $descriptions = getDescriptions(array('dt_studies_trans'), DB_CONNECTION);
    $values = array();
    $q = "SELECT DISTINCT Name FROM Studies LIMIT ".PAGE_ITEM_LIMIT." OFFSET $offset";
    $set = mysql_query($q, DB_CONNECTION);
    while($r = mysql_fetch_row($set)){
      $r = $r[0];
      $entry = array(
        $tid                  //'TranslationId'
      , 'Studies'             //'TableSuffix'
      , $r                    //'Study'
      , $r                    //'Key'
      , array('Name' => $r)); //'Source'
      $translation = array();
      $q = "SELECT Trans FROM Page_DynamicTranslation_Studies WHERE TranslationId = $tid AND Study = '$r'";
      if($t = mysql_fetch_row(mysql_query($q, DB_CONNECTION))){
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
    mysql_query($q, DB_CONNECTION);
    $trans = $translation['Name'];
    $q = "INSERT INTO Page_DynamicTranslation_Studies(TranslationId, Study, Trans) "
       . "VALUES ($tid, '$key', '$trans')";
    mysql_query($q, DB_CONNECTION);
  }
?>
