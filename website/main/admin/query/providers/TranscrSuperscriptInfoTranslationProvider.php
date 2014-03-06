<?php
  /***/
  require_once "DynamicTranslationProvider.php";
  class TranscrSuperscriptInfoTranslationProvider extends DynamicTranslationProvider{
    public function getTable(){ return 'Page_DynamicTranslation_TranscrSuperscriptInfo';}
    public function searchColumn($c, $tId, $searchText){
      //Setup
      $ret = array();
      $tCol = $this->translateColumn($c);
      $description = $tCol['description'];
      $origCol = $tCol['origCol'];
      //Search queries:
      $qs = array("SELECT Ix, $c FROM Page_DynamicTranslation_TranscrSuperscriptInfo "
          . "WHERE $c LIKE '%$searchText%' "
          . "AND TranslationId = $tId");
      if($this->searchAllTranslations()){
        array_push($qs,
          "SELECT Ix, $origCol FROM TranscrSuperscriptInfo "
        . "WHERE $origCol LIKE '%$searchText%'"
        );
      }
      //Search results:
      foreach($this->runQueries($qs) as $r){
        $iX    = $r[0];
        $match = $r[1];
        $q = "SELECT $origCol "
           . "FROM TranscrSuperscriptInfo "
           . "WHERE Ix = $iX";
        $original = $this->querySingleRow($q);
        $q = "SELECT Trans_Abbreviation "
           . "FROM Page_DynamicTranslation_TranscrSuperscriptInfo "
           . "WHERE TranslationId = $tId "
           . "AND Ix = $tId";
        $translation = $this->querySingleRow($q);
        array_push($ret, array(
          'Description' => $description
        , 'Match'       => $match
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
    public function updateColumn($c, $tId, $payload, $update){
      $db     = $this->dbConnection;
      $Ix     = $db->escape_string($payload);
      $update = $db->escape_string($update);
      //Checking existence:
      $q = "SELECT COUNT(*) "
         . "FROM Page_DynamicTranslation_TranscrSuperscriptInfo "
         . "WHERE TranslationId = $tId "
         . "AND Ix = $Ix";
      $exists = $this->querySingleRow($q);
      $exists = $exists[0] > 0;
      //Saving translation:
      if($exists){
        $q = "UPDATE Page_DynamicTranslation_TranscrSuperscriptInfo "
           . "SET Trans_Abbreviation = '$update' "
           . "WHERE TranslationId = $tId "
           . "AND Ix = $Ix";
      }else{
        $q = "INSERT INTO Page_DynamicTranslation_TranscrSuperscriptInfo (TranslationId, Ix, $c) "
           . "VALUES ($tId, $Ix, '$update')";
      }
      $db->query($q);
    }
    public function offsetsColumn($c, $tId, $study){
      $q = "SELECT COUNT(*) FROM TranscrSuperscriptInfo";
      $r = $this->querySingleRow($q);
      return $this->offsetsFromCount(current($r));
    }
    public function pageColumn($c, $tId, $study, $offset){
      //Setup
      $ret  = array();
      $tCol = $this->translateColumn($c);
      $description = $tCol['description'];
      $origCol     = $tCol['origCol'];
      //Page query:
      $q = "SELECT Ix, $origCol "
         . "FROM TranscrSuperscriptInfo LIMIT 30 OFFSET $offset";
      foreach($this->fetchRows($q) as $r){
        $Ix = $r[0];
        $q = "SELECT $c "
           . "FROM Page_DynamicTranslation_TranscrSuperscriptInfo "
           . "WHERE Ix = $Ix "
           . "AND TranslationId = $tId";
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
