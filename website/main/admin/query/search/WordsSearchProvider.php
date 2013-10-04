<?php
  /***/
  require_once "SearchProvider.php";
  class WordsSearchProvider extends SearchProvider{
    public function search($tId, $searchText){
      //Setup
      $ret = array();
      $description = $this->getDescription('dt_words_fullRfcModernLg01');
      //We need all Studies to build the search queries:
      $q = 'SELECT Name FROM Studies';
      $studies = $this->dbConnection->query($q);
      $studies = $this->fetchRows($studies);
      //Search queries:
      $qs = array(
        "SELECT Trans_FullRfcModernLg01, Study, "
      . "CONCAT(IxElicitation, IxMorphologicalInstance) "
      . "FROM Page_DynamicTranslation_Words "
      . "WHERE TranslationId = $tId "
      . "AND Study = ANY(SELECT Name FROM Studies) "
      . "AND Trans_FullRfcModernLg01 LIKE '%$searchText%'"
      );
      foreach($studies as $s){
        $s = $s[0];
        $q = "SELECT FullRfcModernLg01, '$s', "
           . "CONCAT(IxElicitation, IxMorphologicalInstance) "
           . "FROM Words_$s "
           . "WHERE FullRfcModernLg01 LIKE '%$searchText%'";
        array_push($qs, $q);
      }
      //Search results:
      foreach($this->runQueries($qs) as $r){
        $match = $r[0];
        $study = $r[1];
        $wId   = $r[2];
        $q = "SELECT FullRfcModernLg01 "
           . "FROM Words_$study "
           . "WHERE CONCAT(IxElicitation, "
           . "IxMorphologicalInstance) = $wId";
        $original = $this->querySingleRow($q);
        $q = "SELECT Trans_FullRfcModernLg01 "
           . "FROM Page_DynamicTranslation_Words "
           . "WHERE TranslationId = $tId "
           . "AND Study = '$study' "
           . "AND CONCAT(IxElicitation, "
           . "IxMorphologicalInstance) = $wId";
        $translation = $this->querySingleRow($q);
        array_push($ret, array(
          'Description' => $description
        , 'Match'       => $match
        , 'Original'    => $original[0]
        , 'Translation' => array(
            'TranslationId'  => $tId
          , 'Translation'    => $translation[0]
          , 'Payload'        => implode(',', array($study, $wId))
          , 'SearchProvider' => $this->getName()
          )
        ));
      }
      return $ret;
    }
    public function update($tId, $payload, $update){
      $db      = $this->dbConnection;
      $payload = explode(',', $payload);
      $study   = $db->escape_string($payload[0]);
      $wId     = $db->escape_string($payload[1]);
      $update  = $db->escape_string($update);
      $q = "SELECT IxElicitation, IxMorphologicalInstance "
         . "FROM Words_$study "
         . "WHERE CONCAT(IxElicitation, "
         . "IxMorphologicalInstance) = $wId";
      $r   = $this->querySingleRow($q);
      $ixe = $r[0];
      $ixm = $r[1];
      $qs  = array(
        "DELETE FROM Page_DynamicTranslation_Words "
      . "WHERE Study = '$study' "
      . "AND TranslationId = $tId "
      . "AND CONCAT(IxElicitation, "
      . "IxMorphologicalInstance) = $wId"
      , "INSERT INTO Page_DynamicTranslation_Words "
      . "(TranslationId, Study, "
      . "IxElicitation, IxMorphologicalInstance, "
      . "Trans_FullRfcModernLg01) "
      . "VALUES ($tId, '$study', $ixe, $ixm, '$update')"
      );
      foreach($qs as $q)
        $db->query($q);
    }
  }
?>
