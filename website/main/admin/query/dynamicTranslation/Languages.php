<?php
  /***/
  function fetchTranslations_Languages($tid, $study, $offset){
    $descriptions = getDescriptions(array('dt_languages_shortName'
                                         ,'dt_languages_spellingRfcLangName'
                                         ,'dt_languages_specificLanguageVarietyName')
                                   , DB_CONNECTION);
    $values = array();
    $q = "SELECT LanguageIx, ShortName, SpellingRfcLangName, SpecificLanguageVarietyName "
       . "FROM Languages_$study LIMIT ".PAGE_ITEM_LIMIT." OFFSET $offset";
    $set = DB_CONNECTION->query($q);
    while($r = $set->fetch_row()){
      $lid = $r[0];
      //The basic entry:
      $entry = array(
        $tid        //'TranslationId'
      , 'Languages' //'TableSuffix'
      , $study      //'Study'
      , $lid        //'Key'
      );
      //Enritching $entry with source values:
      $source = array(
        'ShortName'                   => $r[1]
      , 'SpellingRfcLangName'         => $r[2]
      , 'SpecificLanguageVarietyName' => $r[3]
      );
      array_push($entry, $source);
      //Enritching $entry with existing translations:
      $translation = array();
      $q = "SELECT Trans_ShortName, Trans_SpellingRfcLangName, "
         . "Trans_SpecificLanguageVarietyName "
         . "FROM Page_DynamicTranslation_Languages "
         . "WHERE TranslationId = $tid AND LanguageIx = $lid AND Study = '$study'";
      if($t = DB_CONNECTION->query($q)->fetch_row()){
        $translation = array(
          'ShortName'                                      => $t[0]
        , 'SpellingRfcLangName'                            => $t[1]
        , 'SpecificLanguageVarietyName'                    => $t[2]
        );
      }
      array_push($entry, $translation, $descriptions);
      //Done
      array_push($values, $entry);
    }
    return $values;
  }
  /***/
  function saveTranslation_Languages($tid, $study, $key, $translation){
    $key = $key[0];
    $q = "DELETE FROM Page_DynamicTranslation_Languages "
       . "WHERE TranslationId = $tid AND Study = '$study' AND LanguageIx = $key";
    DB_CONNECTION->query($q);
    $a = $translation['ShortName'];
    $b = $translation['SpellingRfcLangName'];
    $c = $translation['SpecificLanguageVarietyName'];
    $q = "INSERT INTO Page_DynamicTranslation_Languages("
       . "TranslationId, Study, Trans_ShortName, Trans_SpellingRfcLangName, "
       . "Trans_SpecificLanguageVarietyName, LanguageIx) "
       . "VALUES ($tid, '$study', '$a', '$b', '$c', $key)";
    DB_CONNECTION->query($q);
  }
?>
