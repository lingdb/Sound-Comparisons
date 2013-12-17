<?php
  /***/
  require_once "DynamicSearchProvider.php";
  class WordsSearchProvider extends DynamicSearchProvider{
    public function getTable(){ return 'Page_DynamicTranslation_Words';}
    public function searchColumn($c, $tId, $searchText){
      //Setup
      $ret = array();
      switch($c){
        case 'Trans_FullRfcModernLg01':
          $description = $this->getDescription('dt_words_fullRfcModernLg01');
          $origCol = 'FullRfcModernLg01';
        break;
        case 'Trans_LongerRfcModernLg01':
          $description = $this->getDescription('dt_words_longerRfcModernLg01');
          $origCol = 'LongerRfcModernLg01';
        break;
      }
      //We need all Studies to build the search queries:
      $q = 'SELECT Name FROM Studies';
      $studies = $this->dbConnection->query($q);
      $studies = $this->fetchRows($studies);
      //Search queries:
      $qs = array(
        "SELECT $c, Study, "
      . "CONCAT(IxElicitation, IxMorphologicalInstance) "
      . "FROM Page_DynamicTranslation_Words "
      . "WHERE TranslationId = $tId "
      . "AND Study = ANY(SELECT Name FROM Studies) "
      . "AND $c LIKE '%$searchText%'"
      );
      foreach($studies as $s){
        $s = $s[0];
        $q = "SELECT $origCol, '$s', "
           . "CONCAT(IxElicitation, IxMorphologicalInstance) "
           . "FROM Words_$s "
           . "WHERE $origCol LIKE '%$searchText%'";
        array_push($qs, $q);
      }
      //Search results:
      foreach($this->runQueries($qs) as $r){
        $match = $r[0];
        $study = $r[1];
        $wId   = $r[2];
        $q = "SELECT $origCol "
           . "FROM Words_$study "
           . "WHERE CONCAT(IxElicitation, "
           . "IxMorphologicalInstance) = $wId";
        $original = $this->querySingleRow($q);
        $q = "SELECT $c "
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
    public function updateColumn($c, $tId, $payload, $update){
      $db      = $this->dbConnection;
      $payload = explode(',', $payload);
      $study   = $db->escape_string($payload[0]);
      $wId     = $db->escape_string($payload[1]);
      $update  = $db->escape_string($update);
      //Fetching IxElicitation and IxMorphologicalInstance
      $q = "SELECT IxElicitation, IxMorphologicalInstance "
         . "FROM Words_$study "
         . "WHERE CONCAT(IxElicitation, "
         . "IxMorphologicalInstance) = $wId";
      $r   = $this->querySingleRow($q);
      $ixe = $r[0];
      $ixm = $r[1];
      //Fetching current values:
      $q = "SELECT Trans_FullRfcModernLg01, Trans_LongerRfcModernLg01 "
         . "FROM Page_DynamicTranslation_Words "
         . "WHERE Study = '$study' "
         . "AND TranslationId = $tId "
         . "AND CONCAT(IxElicitation, IxMorphologicalInstance) = $wId";
      $rst = $db->query($q);
      if($r = $rst->fetch_array()){
        $rst = $r;
      }else $rst = array(
        'Trans_FullRfcModernLg01'   => ''
      , 'Trans_LongerRfcModernLg01' => '');
      //Performing the update:
      $rst[$c] = $update;
      $a = $rst['Trans_FullRfcModernLg01'];
      $b = $rst['Trans_LongerRfcModernLg01'];
      $qs  = array(
        "DELETE FROM Page_DynamicTranslation_Words "
      . "WHERE Study = '$study' "
      . "AND TranslationId = $tId "
      . "AND CONCAT(IxElicitation, "
      . "IxMorphologicalInstance) = $wId"
      , "INSERT INTO Page_DynamicTranslation_Words "
      . "(TranslationId, Study, "
      . "IxElicitation, IxMorphologicalInstance, "
      . "Trans_FullRfcModernLg01, Trans_LongerRfcModernLg01) "
      . "VALUES ($tId, '$study', $ixe, $ixm, '$a', '$b')"
      );
      foreach($qs as $q)
        $db->query($q);
    }
  }
?>
