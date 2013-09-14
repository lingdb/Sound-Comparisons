<?php
  /***/
  require_once "DynamicSearchProvider.php";
  class RegionsSearchProvider extends DynamicSearchProvider{
    public function getTable(){return 'Page_DynamicTranslation_Regions';}
    public function searchColumn($c, $tId, $searchText){
      //Setup
      $ret = array();
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
      //We need all Studies to build the search queries:
      $q = 'SELECT Name FROM Studies';
      $studies = $this->dbConnection->query($q);
      $studies = $this->fetchRows($studies);
      //Search queries:
      $qs = array(
        "SELECT $c, Study, RegionIdentifier "
      . "FROM Page_DynamicTranslation_Regions "
      . "WHERE $c LIKE '%$searchText%' "
      . "AND TranslationId = $tId"
      );
      foreach($studies as $s){
        $s = $s[0];
        $q = "SELECT $origCol, '$s', "
           . "CONCAT(StudyIx, FamilyIx, SubFamilyIx, RegionGpIx) "
           . "FROM Regions_$s "
           . "WHERE $origCol LIKE '%$searchText%'";
        array_push($qs, $q);
      }
      //Search results:
      foreach($this->runQueries($qs) as $r){
        $match = $r[0];
        $study = $r[1];
        $rId   = $r[2];
        $q = "SELECT $origCol "
           . "FROM Regions_$study "
           . "WHERE CONCAT(StudyIx, FamilyIx, SubFamilyIx, RegionGpIx) = $rId";
        $original = $this->querySingleRow($q);
        $q = "SELECT $c "
           . "FROM Page_DynamicTranslation_Regions "
           . "WHERE TranslationId = $tId "
           . "AND Study = '$study' "
           . "AND RegionIdentifier = $rId";
        $translation = $this->querySingleRow($q);
        array_push($ret, array(
          'Description' => $description
        , 'Match'       => $match
        , 'Original'    => $original[0]
        , 'Translation' => array(
            'TranslationId'  => $tId
          , 'Translation'    => $translation[0]
          , 'Payload'        => implode(',', array($study, $rId))
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
      $rId     = $db->escape_string($payload[1]);
      $update  = $db->escape_string($update);
      $q = "SELECT Trans_RegionGpNameShort, "
         . "Trans_RegionGpNameLong "
         . "FROM Page_DynamicTranslation_Regions "
         . "WHERE TranslationId = $tId "
         . "AND Study = '$study' "
         . "AND RegionIdentifier = $rId";
      $rst = $db->query($q);
      if($r = $rst->fetch_array()){
        $rst = $r;
      }else $rst = array(
        'Trans_RegionGpNameShort' => ''
      , 'Trans_RegionGpNameLong'  => ''
      );
      $rst[$c] = $update;
      $a  = $rst['Trans_RegionGpNameShort'];
      $b  = $rst['Trans_RegionGpNameLong'];
      $qs = array(
        "DELETE FROM Page_DynamicTranslation_Regions "
      . "WHERE TranslationId = $tId "
      . "AND Study = '$study' "
      . "AND RegionIdentifier = $rId"
      , "INSERT INTO Page_DynamicTranslation_Regions "
      . "(TranslationId, Study, RegionIdentifier, "
      . "Trans_RegionGpNameShort, Trans_RegionGpNameLong) "
      . "VALUES ($tId, '$study', $rId, '$a', '$b')"
      );
      foreach($qs as $q)
        $db->query($q);
    }
  }
?>
