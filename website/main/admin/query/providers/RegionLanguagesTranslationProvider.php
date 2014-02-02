<?php
  /***/
  require_once "DynamicTranslationProvider.php";
  class RegionLanguagesTranslationProvider extends DynamicTranslationProvider{
    public function getTable(){return 'Page_DynamicTranslation_RegionLanguages';}
    public function searchColumn($c, $tId, $searchText){
      //Setup
      $ret = array();
      $tCol = $this->translateColumn($c);
      $description = $tCol['description'];
      $origCol = $tCol['origCol'];
      //We need all Studies to build the search queries:
      $q = 'SELECT Name FROM Studies';
      $studies = $this->dbConnection->query($q);
      $studies = $this->fetchRows($studies);
      //Search queries:
      $qs = array(
        "SELECT $c, Study, LanguageIx "
      . "FROM Page_DynamicTranslation_RegionLanguages "
      . "WHERE $c LIKE '%$searchText%' "
      . "AND TranslationId = $tId"
      );
      foreach($studies as $s){
        $s = $s[0];
        $q = "SELECT $origCol, '$s', LanguageIx "
           . "FROM RegionLanguages_$s "
           . "WHERE $origCol LIKE '%$searchText%'";
        array_push($qs, $q);
      }
      //Search results:
      foreach($this->runQueries($qs) as $r){
        $match = $r[0];
        $study = $r[1];
        $lIx   = $r[2];
        $q = "SELECT $origCol "
           . "FROM RegionLanguages_$study "
           . "WHERE LanguageIx = $lIx";
        $original = $this->querySingleRow($q);
        $q = "SELECT $c "
           . "FROM Page_DynamicTranslation_RegionLanguages "
           . "WHERE TranslationId = $tId "
           . "AND LanguageIx = $lIx "
           . "AND Study = '$study'";
        $translation = $this->querySingleRow($q);
        array_push($ret, array(
          'Description' => $description
        , 'Match'       => $match
        , 'Original'    => $original[0]
        , 'Translation' => array(
            'TranslationId'       => $tId
          , 'Translation'         => $translation[0]
          , 'Payload'             => implode(',', array($study, $lIx))
          , 'TranslationProvider' => $this->getName()
          )
        ));
      }
      return $ret;
    }
    public function updateColumn($c, $tId, $payload, $update){
      $db      = $this->dbConnection;
      $payload = explode(',', $payload);
      $study   = $db->escape_string($payload[0]);
      $lIx     = $db->escape_string($payload[1]);
      $update  = $db->escape_string($update);
      $q = "SELECT Trans_RegionGpMemberLgNameShortInThisSubFamilyWebsite, "
         . "Trans_RegionGpMemberLgNameLongInThisSubFamilyWebsite "
         . "FROM Page_DynamicTranslation_RegionLanguages "
         . "WHERE TranslationId = $tId "
         . "AND Study = '$study' "
         . "AND LanguageIx = $lIx";
      $rst = $db->query($q);
      if($r = $rst->fetch_array()){
        $rst = $r;
      }else $rst = array(
        'Trans_RegionGpMemberLgNameShortInThisSubFamilyWebsite' => ''
      , 'Trans_RegionGpMemberLgNameLongInThisSubFamilyWebsite' => ''
      );
      $rst[$c] = $update;
      $a = $rst['Trans_RegionGpMemberLgNameShortInThisSubFamilyWebsite'];
      $b = $rst['Trans_RegionGpMemberLgNameLongInThisSubFamilyWebsite'];
      $qs = array(
        "DELETE FROM Page_DynamicTranslation_RegionLanguages "
      . "WHERE TranslationId = $tId "
      . "AND Study = '$study' "
      . "AND LanguageIx = $lIx"
      , "INSERT INTO Page_DynamicTranslation_RegionLanguages "
      . "(TranslationId, Study, LanguageIx, "
      . "Trans_RegionGpMemberLgNameShortInThisSubFamilyWebsite, "
      . "Trans_RegionGpMemberLgNameLongInThisSubFamilyWebsite) "
      . "VALUES ($tId, '$study', $lIx, '$a', '$b')"
      );
      foreach($qs as $q)
        $db->query($q);
    }
    public function offsetsColumn($c, $tId, $study){
      $q = "SELECT COUNT(*) FROM RegionLanguages_$study "
         . "WHERE RegionGpMemberLgNameShortInThisSubFamilyWebsite != '' "
         . "AND RegionGpMemberLgNameLongInThisSubFamilyWebsite != ''";
      $r = $this->querySingleRow($q);
      return $this->offsetsFromCount(current($r));
    }
    public function pageColumn($c, $tId, $study, $offset){
      //Setup
      $ret = array();
      $tCol = $this->translateColumn($c);
      $description = $tCol['description'];
      $origCol = $tCol['origCol'];
      //Page query:
      $q = "SELECT $origCol, LanguageIx "
         . "FROM RegionLanguages_$s LIMIT 30 OFFSET $offset";
      foreach($this->fetchRows($q) as $r){
        $lIx = $r[1];
        $q = "SELECT $c "
           . "FROM Page_DynamicTranslation_RegionLanguages "
           . "WHERE TranslationId = $tId "
           . "AND LanguageIx = $lIx "
           . "AND Study = '$study'";
        $translation = $this->querySingleRow($q);
        array_push($ret, array(
          'Description' => $description
        , 'Original'    => $r[0]
        , 'Translation' => array(
            'TranslationId'       => $tId
          , 'Translation'         => $translation[0]
          , 'Payload'             => implode(',', array($study, $lIx))
          , 'TranslationProvider' => $this->getName()
          )
        ));
      }
      return $ret;
    }
    public function translateColumn($c){
      switch($c){
        case 'Trans_RegionGpMemberLgNameShortInThisSubFamilyWebsite':
          $description = $this->getDescription('dt_regionLanguages_RegionGpMemberLgNameShortInThisSubFamilyWebsite');
          $origCol = 'RegionGpMemberLgNameShortInThisSubFamilyWebsite';
        break;
        case 'Trans_RegionGpMemberLgNameLongInThisSubFamilyWebsite':
          $description = $this->getDescription('dt_regionLanguages_RegionGpMemberLgNameLongInThisSubFamilyWebsite');
          $origCol = 'RegionGpMemberLgNameLongInThisSubFamilyWebsite';
        break;
      }
      return array('description' => $description,'origCol' => $origCol);
    }
    public function deleteTranslation($tId){
      $q = "DELETE FROM Page_DynamicTranslation_RegionLanguages WHERE TranslationId = $tId";
      $this->dbConnection->query($q);
    }
  }
?>
