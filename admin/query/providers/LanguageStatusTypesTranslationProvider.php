<?php
  /***/
  require_once "DynamicTranslationProvider.php";
  /*
    Mapping between tables LanguageStatusTypes, Page_DynamicTranslation:
    LanguageStatusType <-> Field
    $c (column)        <-> Trans
  */
  class LanguageStatusTypesTranslationProvider extends DynamicTranslationProvider{
    public function migrate(){
      $category = $this->getName();
      $column   = $this->getColumn();
      $q = "INSERT INTO Page_DynamicTranslation (TranslationId, Category, Field, Trans) "
         . "SELECT TranslationId, '$category', LanguageStatusType, $column "
         . "FROM Page_DynamicTranslation_LanguageStatusTypes";
      $this->dbConnection->query($q);
    }
    public function getTable(){return 'LanguageStatusTypes';}
    public function searchColumn($c, $tId, $searchText){
      //Setup:
      $ret         = array();
      $tCols       = $this->translateColumn($c);
      $description = $tCols['description'];
      $origCol     = $tCols['origCol'];
      //Search queries:
      $qs = array($this->translationSearchQuery($tId, $searchText));
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
        $q = $this->getTranslationQuery($payload, $tId);
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
        $q = $this->getTranslationQuery($r[0], $tId);
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
  }
?>
