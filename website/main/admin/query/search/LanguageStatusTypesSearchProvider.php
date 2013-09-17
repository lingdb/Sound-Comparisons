<?php
  /***/
  require_once "DynamicSearchProvider.php";
  class LanguageStatusTypesSearchProvider extends DynamicSearchProvider{
    public function getTable(){return 'Page_DynamicTranslation_LanguageStatusTypes';}
    public function searchColumn($c, $tId, $searchText){
      //Setup:
      $ret = array();
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
      //Search queries:
      $qs = array(
        "SELECT LanguageStatusType, $c "
      . "FROM Page_DynamicTranslation_LanguageStatusTypes "
      . "WHERE TranslationId = $tId "
      . "AND $c LIKE '%$searchText%'"
      , "SELECT LanguageStatusType, $origCol "
      . "FROM LanguageStatusTypes "
      . "WHERE $origCol LIKE '%$searchText%'"
      );
      //Search results:
      foreach($this->runQueries($qs) as $r){
        $payload = $r[0];
        $match   = $r[1];
        $q = "SELECT $origCol "
           . "FROM LanguageStatusTypes "
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
        , 'Original'    => $original[0]
        , 'Translation' => array(
            'TranslationId'  => $tId
          , 'Translation'    => $translation[0]
          , 'Payload'        => $payload
          , 'SearchProvider' => $this->getName()
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
  }
?>
