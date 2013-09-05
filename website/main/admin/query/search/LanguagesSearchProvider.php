<?php
  /***/
  require_once "DynamicSearchProvider.php";
  class LanguagesSearchProvider extends DynamicSearchProvider{
    public function getTable(){return 'Page_DynamicTranslation_Languages';}
    public function searchColumn($c, $tId, $searchText){
      //Setup
      $ret = array();
      switch($c){
        case 'Trans_ShortName':
          $description = $this->getDescription('dt_languages_shortName');
          $origCol = 'ShortName';
        break;
        case 'Trans_SpellingRfcLangName':
          $description = $this->getDescription('dt_languages_specificLanguageVarietyName');
          $origCol = 'SpellingRfcLangName';
        break;
        case 'Trans_SpecificLanguageVarietyName':
          $description = $this->getDescription('dt_languages_spellingRfcLangName');
          $origCol = 'SpecificLanguageVarietyName';
        break;
      }
      //We need all Studies to build the search queries:
      $q = 'SELECT Name FROM Studies';
      $studies = mysql_query($q, $this->dbConnection);
      $studies = $this->fetchRows($studies);
      //Search queries:
      $qs = array(
        "SELECT $c, Study, LanguageIx "
      . "FROM Page_DynamicTranslation_Languages "
      . "WHERE TranslationId = $tId "
      . "AND $c LIKE '%$searchText%'"
      );
      foreach($studies as $s){
        $s = $s[0];
        $q = "SELECT $origCol, '$s', LanguageIx "
           . "FROM Languages_$s "
           . "WHERE $origCol LIKE '%$searchText%'";
        array_push($qs, $q);
      }
      //Search results:
      foreach($this->runQueries($qs) as $r){
        $match = $r[0];
        $study = $r[1];
        $lIx   = $r[2];
        $q = "SELECT $origCol "
           . "FROM Languages_$study "
           . "WHERE LanguageIx = $lIx";
        $original = $this->querySingleRow($q);
        $q = "SELECT $c "
           . "FROM Page_DynamicTranslation_Languages "
           . "WHERE TranslationId = $tId "
           . "AND Study = '$study' "
           . "AND LanguageIx = $lIx";
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
      $q = "SELECT Trans_ShortName, "
         . "Trans_SpellingRfcLangName, "
         . "Trans_SpecificLanguageVarietyName "
         . "FROM Page_DynamicTranslation_Languages "
         . "WHERE Study = '$study' "
         . "AND TranslationId = $tId "
         . "AND LanguageIx = $lIx";
      $rst = mysql_query($q, $this->dbConnection);
      if($r = mysql_fetch_array($rst)){
        $rst = $r;
      }else $rst = array('Trans_ShortName'    => ''
        , 'Trans_SpellingRfcLangName'         => ''
        , 'Trans_SpecificLanguageVarietyName' => '');
      $rst[$c] = $update;
      $a  = $rst['Trans_ShortName'];
      $b  = $rst['Trans_SpellingRfcLangName'];
      $c  = $rst['Trans_SpecificLanguageVarietyName'];
      $qs = array(
        "DELETE FROM Page_DynamicTranslation_Languages "
      . "WHERE TranslationId = $tId "
      . "AND Study = '$study' "
      . "AND LanguageIx = $lIx"
      , "INSERT INTO Page_DynamicTranslation_Languages "
      . "(TranslationId, Study, Trans_ShortName, "
      . "Trans_SpellingRfcLangName, "
      . "Trans_SpecificLanguageVarietyName, LanguageIx) "
      . "VALUES ($tId, '$study', '$a', '$b', '$c', $lIx)"
      );
      foreach($qs as $q) mysql_query($q, $this->dbConnection);
    }
  }
?>
