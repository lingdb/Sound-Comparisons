<?php
  /***/
  require_once "DynamicTranslationProvider.php";
  /*
    Mapping between tables TranscrSuperscriptInfo and Page_DynamicTranslation:
    Ix          <-> Field
    $c (column) <-> Trans
  */
  class TranscrSuperscriptInfoTranslationProvider extends DynamicTranslationProvider{
    public function migrate(){
      $category = $this->getName();
      $column   = $this->getColumn();
      $q = "INSERT INTO Page_DynamicTranslation (TranslationId, Category, Field, Trans) "
         . "SELECT TranslationId, '$category', Ix, $column "
         . "FROM Page_DynamicTranslation_TranscrSuperscriptInfo";
      $this->dbConnection->query($q);
    }
    public function getTable(){ return 'TranscrSuperscriptInfo';}
    public function searchColumn($c, $tId, $searchText){
      //Setup
      $ret         = array();
      $tCol        = $this->translateColumn($c);
      $description = $tCol['description'];
      $origCol     = $tCol['origCol'];
      //Search queries:
      $qs = array($this->translationSearchQuery($tId, $searchText));
      if($this->searchAllTranslations()){
        array_push($qs,
          "SELECT Ix, $origCol, 1 FROM TranscrSuperscriptInfo "
        . "WHERE $origCol LIKE '%$searchText%'"
        );
      }
      //Search results:
      foreach($this->runQueries($qs) as $r){
        $Ix      = $r[0];
        $match   = $r[1];
        $matchId = $r[2];
        $q = "SELECT $origCol "
           . "FROM TranscrSuperscriptInfo "
           . "WHERE Ix = $Ix";
        $original = $this->querySingleRow($q);
        $q = $this->getTranslationQuery($Ix, $tId);
        $translation = $this->querySingleRow($q);
        array_push($ret, array(
          'Description' => $description
        , 'Match'       => $match
        , 'MatchId'     => $matchId
        , 'Original'    => $original[0]
        , 'Translation' => array(
            'TranslationId'       => $tId
          , 'Translation'         => $translation[0]
          , 'Payload'             => $Ix
          , 'TranslationProvider' => $this->getName()
          )
        ));
      }
      return $ret;
    }
    public function offsetsColumn($c, $tId, $study){
      $q = "SELECT COUNT(*) FROM TranscrSuperscriptInfo";
      $r = $this->querySingleRow($q);
      return $this->offsetsFromCount(current($r));
    }
    public function pageColumn($c, $tId, $study, $offset){
      //Setup
      $ret         = array();
      $tCol        = $this->translateColumn($c);
      $description = $tCol['description'];
      $origCol     = $tCol['origCol'];
      //Page query:
      $q = "SELECT Ix, $origCol "
         . "FROM TranscrSuperscriptInfo LIMIT 30 OFFSET $offset";
      foreach($this->fetchRows($q) as $r){
        $Ix = $r[0];
        $q  = $this->getTranslationQuery($Ix, $tId);
        $translation = $this->querySingleRow($q);
        array_push($ret, array(
          'Description' => $description
        , 'Original'    => $r[1]
        , 'Translation' => array(
            'TranslationId'       => $tId
          , 'Translation'         => $translation[0]
          , 'Payload'             => $Ix
          , 'TranslationProvider' => $this->getName()
          )
        ));
      }
      return $ret;
    }
    public function translateColumn($c){
      switch($c){
        case 'Trans_Abbreviation':
          $description = $this->getDescription('dt_superscriptInfo_abbreviation');
          $origCol = 'Abbreviation';
        break;
        case 'Trans_HoverText':
          $description = $this->getDescription('dt_superscriptInfo_hoverText');
          $origCol = 'HoverText';
        break;
      }
      return array('description' => $description, 'origCol' => $origCol);
    }
  }
?>