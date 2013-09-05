<?php
  /***/
  function fetchTranslations_Words($tid, $study, $offset){
    $q = "SELECT SecondRfcLg FROM Studies WHERE Name = '$study'";
    $r = mysql_fetch_row(mysql_query($q, DB_CONNECTION));
    $rfcLg = ($r[0] === '') ? null : $r[0];
    $descriptions = getDescriptions(array('dt_words_fullRfcModernLg01'), DB_CONNECTION);
    $values = array();
    $q = "SELECT IxElicitation, IxMorphologicalInstance, "
       . "FullRfcModernLg01, FullRfcModernLg02 "
       . "FROM Words_$study LIMIT "
       . PAGE_ITEM_LIMIT
       . " OFFSET $offset";
    $set = mysql_query($q, DB_CONNECTION);
    while($r = mysql_fetch_row($set)){
      if($r[3] !== $r[2] && $r[3] !== ''){
        $rfcForm = ($rfcLg === null) ? array() : array('rfc' => $rfcLg, 'form' => $r[3]);
      }else $rfcForm = array();
      $entry = array(
        $tid                            //'TranslationId'
      , 'Words'                         //'TableSuffix'
      , $study                          //'Study'
      , implode(',',array($r[0],$r[1])) //'Key'
      , array(                          //'Source'
          'FullRfcModernLg01' => $r[2]
        , $rfcForm));
      $translation = array();
      $q = "SELECT Trans_FullRfcModernLg01 "
         . "FROM Page_DynamicTranslation_Words "
         . "WHERE TranslationId = $tid "
         . "AND Study='$study' "
         . "AND IxElicitation = ".$r[0]." "
         . "AND IxMorphologicalInstance = ".$r[1];
      if($t = mysql_fetch_row(mysql_query($q, DB_CONNECTION))){
        $translation = array('FullRfcModernLg01' => $t[0]);
      }
      array_push($entry, $translation, $descriptions);
      array_push($values, $entry);
    }
    return $values;
  }
  /***/
  function saveTranslation_Words($tid, $study, $key, $translation){
    $ixe = $key[0];
    $ixm = $key[1];
    $q   = "DELETE FROM Page_DynamicTranslation_Words "
         . "WHERE TranslationId = $tid "
         . "AND Study = '$study' "
         . "AND IxElicitation = $ixe "
         . "AND IxMorphologicalInstance = $ixm";
    mysql_query($q, DB_CONNECTION);
    $a = $translation['FullRfcModernLg01'];
    $q = "INSERT INTO Page_DynamicTranslation_Words(TranslationId, "
       . "Study, IxElicitation, IxMorphologicalInstance, "
       . "Trans_FullRfcModernLg01) "
       . "VALUES ($tid, '$study', $ixe, $ixm, '$a')";
    mysql_query($q, DB_CONNECTION);
  }
?>
