<?php
  /***/
  function fetchTranslations_Words($dbConnection, $tid, $study, $offset){
    $q = "SELECT SecondRfcLg FROM Studies WHERE Name = '$study'";
    $r = $dbConnection->query($q)->fetch_row();
    $rfcLg = ($r[0] === '') ? null : $r[0];
    $descriptions = getDescriptions(array('dt_words_fullRfcModernLg01','dt_words_longerRfcModernLg01'), $dbConnection);
    $values = array();
    $q = "SELECT IxElicitation, IxMorphologicalInstance, "
       . "FullRfcModernLg01, FullRfcModernLg02, "
       . "LongerRfcModernLg01, LongerRfcModernLg02 "
       . "FROM Words_$study LIMIT "
       . PAGE_ITEM_LIMIT
       . " OFFSET $offset";
    $set = $dbConnection->query($q);
    while($r = $set->fetch_row()){
      if($r[3] !== $r[2] && $r[3] !== ''){
        $rfcForm1 = ($rfcLg === null) ? array() : array('rfc' => $rfcLg, 'form' => $r[3]);
      }else $rfcForm1 = array();
      if($r[4] !== $r[5] && $r[5] !== ''){
        $rfcForm2 = ($rfcLg === null) ? array() : array('rfc' => $rfcLg, 'form' => $r[5]);
      } else $rfcForm2 = array();
      $entry = array(
        $tid                            //'TranslationId'
      , 'Words'                         //'TableSuffix'
      , $study                          //'Study'
      , implode(',',array($r[0],$r[1])) //'Key'
      , array(                          //'Source'
          'FullRfcModernLg01' => $r[2]
        , $rfcForm1
        , 'LongerRfcModernLg01' => $r[4]
        , $rfcForm2));
      $translation = array();
      $q = "SELECT Trans_FullRfcModernLg01, Trans_LongerRfcModernLg01 "
         . "FROM Page_DynamicTranslation_Words "
         . "WHERE TranslationId = $tid "
         . "AND Study='$study' "
         . "AND IxElicitation = ".$r[0]." "
         . "AND IxMorphologicalInstance = ".$r[1];
      if($t = $dbConnection->query($q)->fetch_row()){
        $translation = array('FullRfcModernLg01' => $t[0], 'LongerRfcModernLg01' => $t[1]);
      }
      array_push($entry, $translation, $descriptions);
      array_push($values, $entry);
    }
    return $values;
  }
  /***/
  function saveTranslation_Words($dbConnection, $tid, $study, $key, $translation){
    $ixe = $key[0];
    $ixm = $key[1];
    $q   = "DELETE FROM Page_DynamicTranslation_Words "
         . "WHERE TranslationId = $tid "
         . "AND Study = '$study' "
         . "AND IxElicitation = $ixe "
         . "AND IxMorphologicalInstance = $ixm";
    $dbConnection->query($q);
    $a = $translation['FullRfcModernLg01'];
    $b = $translation['LongerRfcModernLg01'];
    $q = "INSERT INTO Page_DynamicTranslation_Words(TranslationId, "
       . "Study, IxElicitation, IxMorphologicalInstance, "
       . "Trans_FullRfcModernLg01, Trans_LongerRfcModernLg01) "
       . "VALUES ($tid, '$study', $ixe, $ixm, '$a', '$b')";
    $dbConnection->query($q);
  }
?>
