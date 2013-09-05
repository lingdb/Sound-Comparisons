<?php
  /***/
  function fetchTranslations_RegionLanguages($tid, $study, $offset){
    $descriptions = getDescriptions(array('dt_regionLanguages_RegionGpMemberLgNameShortInThisSubFamilyWebsite'
                                         ,'dt_regionLanguages_RegionGpMemberLgNameLongInThisSubFamilyWebsite')
                                   , DB_CONNECTION);
    $values = array();
    $q = "SELECT LanguageIx, RegionGpMemberLgNameShortInThisSubFamilyWebsite"
       . ", RegionGpMemberLgNameLongInThisSubFamilyWebsite "
       . "FROM RegionLanguages_$study "
       . "WHERE RegionGpMemberLgNameShortInThisSubFamilyWebsite != '' "
       . "AND RegionGpMemberLgNameLongInThisSubFamilyWebsite != '' "
       . "LIMIT ".PAGE_ITEM_LIMIT." OFFSET $offset";
    $set = mysql_query($q, DB_CONNECTION);
    while($r = mysql_fetch_row($set)){
      $entry = array(
        $tid              //'TranslationId'
      , 'RegionLanguages' //'TableSuffix'
      , $study            //'Study'
      , $r[0]             //'Key'
      , array(            //'Source'
          'RegionGpMemberLgNameShortInThisSubFamilyWebsite' => $r[1]
        , 'RegionGpMemberLgNameLongInThisSubFamilyWebsite'  => $r[2]
      ));
      $translation = array();
      $q = "SELECT Trans_RegionGpMemberLgNameShortInThisSubFamilyWebsite, "
         . "Trans_RegionGpMemberLgNameLongInThisSubFamilyWebsite "
         . "FROM Page_DynamicTranslation_RegionLanguages "
         . "WHERE TranslationId = $tid AND Study = '$study' "
         . "AND LanguageIx = ".$r[0];
      if($t = mysql_fetch_row(mysql_query($q, DB_CONNECTION))){
        $translation = array(
          'RegionGpMemberLgNameShortInThisSubFamilyWebsite' => $t[0]
        , 'RegionGpMemberLgNameLongInThisSubFamilyWebsite'  => $t[1]
        );
      }
      array_push($entry, $translation, $descriptions);
      array_push($values, $entry);
    }
    return $values;
  }
  /***/
  function saveTranslation_RegionLanguages($tid, $study, $key, $translation){
    $key = $key[0];
    $q = "DELETE FROM Page_DynamicTranslation_RegionLanguages "
       . "WHERE TranslationId = $tid AND Study = '$study' "
       . "AND LanguageIx = $key";
    mysql_query($q, DB_CONNECTION);
    $a = $translation['RegionGpMemberLgNameShortInThisSubFamilyWebsite'];
    $b = $translation['RegionGpMemberLgNameLongInThisSubFamilyWebsite'];
    $q = "INSERT INTO Page_DynamicTranslation_RegionLanguages(TranslationId, Study, LanguageIx, "
       . "Trans_RegionGpMemberLgNameShortInThisSubFamilyWebsite, "
       . "Trans_RegionGpMemberLgNameLongInThisSubFamilyWebsite) "
       . "VALUES ($tid, '$study', $key, '$a', '$b')";
    mysql_query($q, DB_CONNECTION);
  }
?>
