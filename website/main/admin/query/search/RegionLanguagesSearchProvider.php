<?php
  /***/
  require_once "DynamicSearchProvider.php";
  class RegionLanguagesSearchProvider extends DynamicSearchProvider{
    public function getTable(){return 'Page_DynamicTranslation_RegionLanguages';}
    public function searchColumn($c, $tId, $searchText){
      //Setup
      $ret = array();
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
      //We need all Studies to build the search queries:
      $q = 'SELECT Name FROM Studies';
      $studies = mysql_query($q, $this->dbConnection);
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
            'TranslationId'  => $tId
          , 'Translation'    => $translation[0]
          , 'Payload'        => implode(',', array($study, $lIx))
          , 'SearchProvider' => $this->getName()
          )
        ));
      }
      return $ret;
    }
    public function updateColumn($c, $tId, $payload, $update){
      $payload = explode(',', $payload);
      $study   = mysql_real_escape_string($payload[0]);
      $lIx     = mysql_real_escape_string($payload[1]);
      $update  = mysql_real_escape_string($update);
      $q = "SELECT Trans_RegionGpMemberLgNameShortInThisSubFamilyWebsite, "
         . "Trans_RegionGpMemberLgNameLongInThisSubFamilyWebsite "
         . "FROM Page_DynamicTranslation_RegionLanguages "
         . "WHERE TranslationId = $tId "
         . "AND Study = '$study' "
         . "AND LanguageIx = $lIx";
      $rst = mysql_query($q, $this->dbConnection);
      if($r = mysql_fetch_array($rst)){
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
      foreach($qs as $q) mysql_query($q, $this->dbConnection);
    }
  }
?>
