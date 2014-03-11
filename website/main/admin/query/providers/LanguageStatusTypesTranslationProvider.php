<?php
  /***/
  require_once "DynamicTranslationProvider.php";
  class LanguageStatusTypesTranslationProvider extends DynamicTranslationProvider{
    public function getTable(){return 'Page_DynamicTranslation_LanguageStatusTypes';}
    public function searchColumn($c, $tId, $searchText){
      //Setup:
      $ret = array();
      $tCols = $this->translateColumn($c);
      $description = $tCols['description'];
      $origCol = $tCols['origCol'];
      //Search queries:
      $qs = array("SELECT LanguageStatusType, $c, TranslationId "
          . "FROM Page_DynamicTranslation_LanguageStatusTypes "
          . "WHERE TranslationId = $tId AND $c LIKE '%$searchText%'");
      if($this->searchAllTranslations()){
        array_push($qs,
          "SELECT LanguageStatusType, $origCol, 1 FROM LanguageStatusTypes "
        . "WHERE $origCol LIKE '%$searchText%'"
        );
      }
      //Search results:
      foreach($this->runQueries($qs) as $r){
        $payload = $r[0];
        $match   = $r[1];
        $matchId = $r[2];
        $q = "SELECT $origCol FROM LanguageStatusTypes "
           . "WHERE LanguageStatusType = $payload";
        $original = $this->dbConnection->query($q)->fetch_row();
        $q = "SELECT $c FROM "
           . "Page_DynamicTranslation_LanguageStatusTypes "
           . "WHERE TranslationId = $tId "
           . "AND LanguageStatusType = $payload";
        $translation = $this->dbConnection->query($q)->fetch_row();
        array_push($ret, array(
          'Description' => $description
        , 'Match'       => $match
        , 'MatchId'     => $matchId
        , 'Original'    => $original[0]
        , 'Translation' => array(
            'TranslationId'       => $tId
          , 'Translation'         => $translation[0]
          , 'Payload'             => $payload
          , 'TranslationProvider' => $this->getName()
          )
        ));
      }
      return $ret;
    }
    public function updateColumn($c, $tId, $payload, $update){
      $db      = $this->dbConnection;
      $payload = $db->escape_string($payload);
      $update  = $db->escape_string($update);
      $q = "SELECT Trans_Status, Trans_Description, Trans_StatusTooltip "
         . "FROM Page_DynamicTranslation_LanguageStatusTypes "
         . "WHERE TranslationId = $tId "
         . "AND LanguageStatusType = $payload";
      $rst = $db->query($q);
      if($r = $rst->fetch_array()){
        $rst = $r;
      }else $rst = array('Trans_Status'        => ''
                       , 'Trans_Description'   => ''
                       , 'Trans_StatusTooltip' => '');
      $rst[$c] = $update;
      $a  = $rst['Trans_Status'];
      $b  = $rst['Trans_Description'];
      $c  = $rst['Trans_StatusTooltip'];
      $qs = array(
        "DELETE FROM Page_DynamicTranslation_LanguageStatusTypes "
      . "WHERE TranslationId = $tId "
      . "AND LanguageStatusType = $payload"
      , "INSERT INTO Page_DynamicTranslation_LanguageStatusTypes"
      . "(TranslationId, LanguageStatusType, Trans_Status, Trans_Description, Trans_StatusTooltip) "
      . "VALUES ($tId, $payload, '$a', '$b', '$c')"
      );
      foreach($qs as $q)
        $db->query($q);
    }
    public function offsetsColumn($c, $tId, $study){
      $q = "SELECT COUNT(*) FROM LanguageStatusTypes WHERE Description != ''";
      $r = $this->querySingleRow($q);
      return $this->offsetsFromCount(current($r));
    }
    public function pageColumn($c, $tId, $study, $offset){
      //Setup:
      $ret = array();
      $tCols = $this->translateColumn($c);
      $description = $tCols['description'];
      $origCol = $tCols['origCol'];
      //Page query:
      $q = "SELECT LanguageStatusType, $origCol "
         . "FROM LanguageStatusTypes LIMIT 30 OFFSET $offset";
      foreach($this->fetchRows($q) as $r){
        $q = "SELECT $c FROM "
           . "Page_DynamicTranslation_LanguageStatusTypes "
           . "WHERE TranslationId = $tId "
           . "AND LanguageStatusType = ".$r[0];
        $translation = $this->dbConnection->query($q)->fetch_row();
        array_push($ret, array(
          'Description' => $description
        , 'Original'    => $r[1]
        , 'Translation' => array(
            'TranslationId'       => $tId
          , 'Translation'         => $translation[0]
          , 'Payload'             => $r[0]
          , 'TranslationProvider' => $this->getName()
          )
        ));
      }
      return $ret;
    }
    public function translateColumn($c){
      switch($c){
        case 'Trans_Status':
          $description = $this->getDescription('dt_languageStatusTypes_status');
          $origCol     = 'Status';
        break;
        case 'Trans_Description':
          $description = $this->getDescription('dt_languageStatusTypes_description');
          $origCol     = 'Description';
        break;
        case 'Trans_StatusTooltip':
          $description = $this->getDescription('dt_languageStatusTypes_statusTooltip');
          $origCol     = 'StatusTooltip';
      }
      return array('description' => $description, 'origCol' => $origCol);
    }
    public function deleteTranslation($tId){
      $q = "DELETE FROM Page_DynamicTranslation_LanguageStatusTypes WHERE TranslationId = $tId";
      $this->dbConnection->query($q);
    }
  }
?>
