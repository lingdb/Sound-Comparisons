<?php
  /***/
  function fetchTranslations_StudyTitle($tid, $study, $offset){
    $descriptions = getDescriptions(array('dt_studyTitle_trans'), DB_CONNECTION);
    $values = array();
    $q = "SELECT DISTINCT Name FROM Studies LIMIT ".PAGE_ITEM_LIMIT." OFFSET $offset";
    $set = DB_CONNECTION->query($q);
    while($r = $set->fetch_row()){
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
      if($t = DB_CONNECTION->query($q)->fetch_row()){
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
    DB_CONNECTION->query($q);
    $trans = $translation['Title'];
    $q = "INSERT INTO Page_DynamicTranslation_StudyTitle(TranslationId, Trans, StudyName) "
       . "VALUES ($tid,'$trans','$key')";
    DB_CONNECTION->query($q);
  }
?>
