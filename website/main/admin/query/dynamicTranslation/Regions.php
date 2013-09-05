<?php
  /***/
  function fetchTranslations_Regions($tid, $study, $offset){
    $descriptions = getDescriptions(array('dt_regions_regionGpNameShort','dt_regions_regionGpNameLong'), DB_CONNECTION);
    $values = array();
    $q = "SELECT CONCAT(StudyIx, FamilyIx, SubFamilyIx, RegionGpIx), RegionGpNameShort, RegionGpNameLong "
       . "FROM Regions_$study LIMIT ".PAGE_ITEM_LIMIT." OFFSET $offset";
    $set = mysql_query($q, DB_CONNECTION);
    while($r = mysql_fetch_row($set)){
      $entry = array(
        $tid      //'TranslationId'
      , 'Regions' //'TableSuffix'
      , $study    //'Study'
      , $r[0]     //'Key'
      , array(    //'Source'
          'RegionGpNameShort' => $r[1]
        , 'RegionGpNameLong'  => $r[2]
      ));
      $translation = array();
      $q = "SELECT Trans_RegionGpNameShort, Trans_RegionGpNameLong "
         . "FROM Page_DynamicTranslation_Regions "
         . "WHERE TranslationId = $tid AND Study = '$study' AND RegionIdentifier = '".$r[0]."'";
      if($t = mysql_fetch_row(mysql_query($q, DB_CONNECTION))){
        $translation = array(
          'RegionGpNameShort' => $t[0]
        , 'RegionGpNameLong'  => $t[1]
        );
      }
      array_push($entry, $translation, $descriptions);
      array_push($values, $entry);
    }
    return $values;
  }
  /***/
  function saveTranslation_Regions($tid, $study, $key, $translation){
    $key = $key[0];
    $q = "DELETE FROM Page_DynamicTranslation_Regions "
       . "WHERE TranslationId = $tid AND Study = '$study' AND RegionIdentifier = '$key'";
    mysql_query($q, DB_CONNECTION);
    $a = $translation['RegionGpNameShort'];
    $b = $translation['RegionGpNameLong'];
    $q = "INSERT INTO Page_DynamicTranslation_Regions(TranslationId, Study, RegionIdentifier, "
       . "Trans_RegionGpNameShort, Trans_RegionGpNameLong) "
       . "VALUES ($tid, '$study', '$key', '$a', '$b')";
    mysql_query($q, DB_CONNECTION);
  }
?>
