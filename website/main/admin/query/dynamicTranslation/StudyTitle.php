<?php
  /***/
  function fetchTranslations_StudyTitle($tid, $study, $offset){
    $descriptions = getDescriptions(array('dt_studyTitle_trans'), DB_CONNECTION);
    $values = array();
    $q = "SELECT DISTINCT Name FROM Studies LIMIT ".PAGE_ITEM_LIMIT." OFFSET $offset";
    $set = mysql_query($q, DB_CONNECTION);
    while($r = mysql_fetch_row($set)){
      $r = $r[0];
      $entry = array(
        $tid                  //'TranslationId'
      , 'StudyTitle'          //'TableSuffix'
      , $r                    //'Study'
      , $r                    //'Key'
      , array('Title' => "Page title for Study: '$r'")); //'Source'
      $translation = array();
      $q = "SELECT Trans FROM Page_DynamicTranslation_StudyTitle "
         . "WHERE TranslationId = $tid AND StudyName = '$r'";
      if($t = mysql_fetch_row(mysql_query($q, DB_CONNECTION))){
        $translation = array('Title' => $t[0]);
      }
      array_push($entry, $translation, $descriptions);
      array_push($values, $entry);
    }
    return $values;
  }
  /***/
  function saveTranslation_StudyTitle($tid, $study, $key, $translation){
    $key = $key[0];
    $q = "DELETE FROM Page_DynamicTranslation_StudyTitle "
       . "WHERE TranslationId = $tid AND StudyName = '$key'";
    mysql_query($q, DB_CONNECTION);
    $trans = $translation['Title'];
    $q = "INSERT INTO Page_DynamicTranslation_StudyTitle(TranslationId, Trans, StudyName) "
       . "VALUES ($tid,'$trans','$key')";
    mysql_query($q, DB_CONNECTION);
  }
?>
