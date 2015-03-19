<?php
  /***/
  require_once "DynamicTranslationProvider.php";
  /*
    Mapping between TranscSuperscriptLenderLgs and Page_DynamicTranslation:
    IsoCode     <-> Field
    $c (column) <-> Trans
  */
  class TranscrSuperscriptLenderLgsTranslationProvider extends DynamicTranslationProvider{
    public function getTable(){ return 'TranscrSuperscriptLenderLgs';}
    public function searchColumn($c, $tId, $searchText, $searchAll = false){
      //Setup
      $ret         = array();
      $tCol        = $this->translateColumn($c);
      $description = $tCol['description'];
      $origCol     = $tCol['origCol'];
      //Search queries:
      $qs = array($this->translationSearchQuery($tId, $searchText));
      if($searchAll){
        array_push($qs,
          "SELECT IsoCode, $origCol, 1 "
        . "FROM TranscrSuperscriptLenderLgs "
        . "WHERE $origCol LIKE '%$searchText%'"
        );
      }
      //Search results:
      foreach($this->runQueries($qs) as $r){
        $iso     = $r[0];
        $match   = $r[1];
        $matchId = $r[2];
        $q = "SELECT $origCol "
           . "FROM TranscrSuperscriptLenderLgs "
           . "WHERE IsoCode = '$iso'";
        $original = $this->querySingleRow($q);
        $q = $this->getTranslationQuery($iso, $tId);
        $translation = $this->querySingleRow($q);
        array_push($ret, array(
          'Description' => $description
        , 'Match'       => $match
        , 'MatchId'     => $matchId
        , 'Original'    => $original[0]
        , 'Translation' => array(
            'TranslationId'       => $tId
          , 'Translation'         => $translation[0]
          , 'Payload'             => $iso
          , 'TranslationProvider' => $this->getName()
          )
        ));
      }
      return $ret;
    }
    public function offsetsColumn($c, $tId, $study){
      $q = "SELECT COUNT(*) FROM TranscrSuperscriptLenderLgs";
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
      $o = ($offset == -1) ? '' : " LIMIT 30 OFFSET $offset";
      $q = "SELECT IsoCode, $origCol FROM TranscrSuperscriptLenderLgs$o";
      foreach($this->fetchRows($q) as $r){
        $iso = $r[0];
        $q   = $this->getTranslationQuery($iso, $tId);
        $translation = $this->querySingleRow($q);
        array_push($ret, array(
          'Description' => $description
        , 'Original'    => $r[1]
        , 'Translation' => array(
            'TranslationId'       => $tId
          , 'Translation'         => $translation[0]
          , 'Payload'             => $iso
          , 'TranslationProvider' => $this->getName()
          )
        ));
      }
      return $ret;
    }
    public function translateColumn($c){
      switch($c){
        case 'Trans_Abbreviation':
          $description = TranslationProvider::getDescription('dt_superscriptLenderLgs_abbreviation');
          $origCol = 'Abbreviation';
        break;
        case 'Trans_FullNameForHoverText':
          $description = TranslationProvider::getDescription('dt_superscriptLenderLgs_fullNameForHoverText');
          $origCol = 'FullNameForHoverText';
        break;
      }
      return array('description' => $description, 'origCol' => $origCol);
    }
  }
?>
