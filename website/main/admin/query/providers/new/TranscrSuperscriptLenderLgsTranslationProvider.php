<?php
  /***/
  require_once "DynamicTranslationProvider.php";
  class TranscrSuperscriptLenderLgsTranslationProvider extends DynamicTranslationProvider{
    public function getTable(){ return 'Page_DynamicTranslation_TranscrSuperscriptLenderLgs';}
    public function searchColumn($c, $tId, $searchText){
      //Setup
      $ret = array();
      $tCol = $this->translateColumn($c);
      $description = $tCol['description'];
      $origCol = $tCol['origCol'];
      //Search queries:
      $qs = array("SELECT IsoCode, $c, TranslationId "
          . "FROM Page_DynamicTranslation_TranscrSuperscriptLenderLgs "
          . "WHERE $c LIKE '%$searchText%' "
          . "AND TranslationId = $tId");
      if($this->searchAllTranslations()){
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
        $q = "SELECT $c "
           . "FROM Page_DynamicTranslation_TranscrSuperscriptLenderLgs "
           . "WHERE IsoCode = '$iso' "
           . "AND TranslationId = $tId";
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
    public function updateColumn($c, $tId, $payload, $update){
      $db     = $this->dbConnection;
      $iso    = $db->escape_string($payload);
      $update = $db->escape_string($update);
      //Checking existence:
      $q = "SELECT COUNT(*) "
         . "FROM Page_DynamicTranslation_TranscrSuperscriptLenderLgs "
         . "WHERE IsoCode = '$iso' "
         . "AND TranslationId = $tId";
      $exists = $this->querySingleRow($q);
      $exists = $exists[0] > 0;
      //Saving translation:
      if($exists){
        $q = "UPDATE Page_DynamicTranslation_TranscrSuperscriptLenderLgs "
           . "SET $c = '$update' "
           . "WHERE IsoCode = '$iso' "
           . "AND TranslationId = $tId";
      }else{
        $q = "INSERT INTO Page_DynamicTranslation_TranscrSuperscriptLenderLgs (TranslationId, IsoCode, $c) "
           . "VALUES ($tId, '$iso', '$update')";
      }
      $db->query($q);
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
      $q = "SELECT IsoCode, $origCol "
         . "FROM TranscrSuperscriptLenderLgs LIMIT 30 OFFSET $offset";
      foreach($this->fetchRows($q) as $r){
        $iso = $r[0];
        $q   = "SELECT $c "
             . "FROM Page_DynamicTranslation_TranscrSuperscriptLenderLgs "
             . "WHERE TranslationId = $tId "
             . "AND IsoCode = '$iso'";
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
          $description = $this->getDescription('dt_superscriptLenderLgs_abbreviation');
          $origCol = 'Abbreviation';
        break;
        case 'Trans_FullNameForHoverText':
          $description = $this->getDescription('dt_superscriptLenderLgs_fullNameForHoverText');
          $origCol = 'FullNameForHoverText';
        break;
      }
      return array('description' => $description, 'origCol' => $origCol);
    }
    public function deleteTranslation($tId){
      $q = "DELETE FROM Page_DynamicTranslation_TranscrSuperscriptLenderLgs WHERE TranslationId = $tId";
      $this->dbConnection->query($q);
    }
  }
?>
