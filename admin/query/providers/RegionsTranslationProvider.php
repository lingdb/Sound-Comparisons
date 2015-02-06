<?php
  /***/
  require_once "DynamicTranslationProvider.php";
  /*
    Mapping between tables Regions_$s and Page_DynamicTranslation:
    CONCAT(Study,'-',StudyIx,FamilyIx,SubFamilyIx,RegionGpIx) <-> Field
    $c (column)                                               <-> Trans
  */
  class RegionsTranslationProvider extends DynamicTranslationProvider{
    public function getTable(){return 'Regions_';}
    public function searchColumn($c, $tId, $searchText, $searchAll = false){
      //Setup
      $ret         = array();
      $tCol        = $this->translateColumn($c);
      $description = $tCol['description'];
      $origCol     = $tCol['origCol'];
      //Search queries:
      $qs = array($this->translationSearchQuery($tId, $searchText));
      if($searchAll){
        //We need all Studies to build the search queries:
        $q   = 'SELECT Name FROM Studies';
        $set = $this->dbConnection->query($q);
        foreach($this->fetchRows($set) as $s){
          $s = $s[0];
          $q = "SELECT CONCAT('$s-', StudyIx, FamilyIx, SubFamilyIx, RegionGpIx), $origCol, 1"
             . "FROM Regions_$s "
             . "WHERE $origCol LIKE '%$searchText%'";
          array_push($qs, $q);
        }
      }
      //Search results:
      foreach($this->runQueries($qs) as $r){
        $payload = $r[0];
        $match   = $r[1];
        $matchId = $r[2];
        $parts   = explode('-', $payload);
        $study   = $parts[0];
        $rId     = $parts[1];
        $q = "SELECT $origCol "
           . "FROM Regions_$study "
           . "WHERE CONCAT(StudyIx, FamilyIx, SubFamilyIx, RegionGpIx) = $rId";
        $original = $this->querySingleRow($q);
        $q = $this->getTranslationQuery($payload, $tId);
        $translation = $this->querySingleRow($q);
        array_push($ret, array(
          'Description' => $description
        , 'Study'       => $study
        , 'Match'       => $match
        , 'MatchId'     => $matchId
        , 'Original'    => $original[0]
        , 'Translation' => array(
            'TranslationId'       => $tId
          , 'Translation'         => $translation[0]
          , 'Payload'             => implode(',', array($study, $rId))
          , 'TranslationProvider' => $this->getName()
          )
        ));
      }
      return $ret;
    }
    public function offsetsColumn($c, $tId, $study){
      $q = "SELECT COUNT(*) FROM Regions_$study";
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
      $o = ($offset == -1) ? '' : " LIMIT 30 OFFSET $offset";
      $q = "SELECT $origCol, CONCAT(StudyIx, FamilyIx, SubFamilyIx, RegionGpIx) "
         . "FROM Regions_$study$o";
      foreach($this->fetchRows($q) as $r){
        $payload = implode('-', array($study, $r[1]));
        $q = $this->getTranslationQuery($payload, $tId);
        $translation = $this->querySingleRow($q);
        array_push($ret, array(
          'Description' => $description
        , 'Original'    => $r[0]
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
    public function translateColumn($c){
      switch($c){
        case 'Trans_RegionGpNameShort':
          $description = $this->getDescription('dt_regions_regionGpNameShort');
          $origCol = 'RegionGpNameShort';
        break;
        case 'Trans_RegionGpNameLong':
          $description = $this->getDescription('dt_regions_regionGpNameLong');
          $origCol = 'RegionGpNameLong';
        break;
      }
      return array('description' => $description, 'origCol' => $origCol);
    }
  }
?>
