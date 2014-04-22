<?php
  /***/
  require_once "DynamicTranslationProvider.php";
  /*
    Mapping between tables Languages_$s, Page_DynamicTranslation:
    CONCAT(Study,'-',LanguageIx) <-> Field
    $c (column)                  <-> Trans
  */
  class LanguagesTranslationProvider extends DynamicTranslationProvider{
    public function getTable(){return 'Page_DynamicTranslation_Languages';}
    public function searchColumn($c, $tId, $searchText){
      //Setup
      $ret = array();
      $tCols = $this->translateColumn($c);
      $description = $tCols['description'];
      $origCol = $tCols['origCol'];
      //We need all Studies to build the search queries:
      $q = 'SELECT Name FROM Studies';
      $studies = $this->dbConnection->query($q);
      $studies = $this->fetchRows($studies);
      //Search queries:
      $qs = array("SELECT $c, Study, LanguageIx, TranslationId "
          . "FROM Page_DynamicTranslation_Languages "
          . "WHERE TranslationId = $tId "
          . "AND $c LIKE '%$searchText%'");
      if($this->searchAllTranslations()){
        foreach($studies as $s){
          $s = $s[0];
          $q = "SELECT $origCol, '$s', LanguageIx, 1 "
             . "FROM Languages_$s "
             . "WHERE $origCol LIKE '%$searchText%'";
          array_push($qs, $q);
        }
      }
      //Search results:
      foreach($this->runQueries($qs) as $r){
        $match   = $r[0];
        $study   = $r[1];
        $lIx     = $r[2];
        $matchId = $r[3];
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
        , 'MatchId'     => $matchId
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
      $q = "SELECT Trans_ShortName, "
         . "Trans_SpellingRfcLangName, "
         . "Trans_SpecificLanguageVarietyName "
         . "FROM Page_DynamicTranslation_Languages "
         . "WHERE Study = '$study' "
         . "AND TranslationId = $tId "
         . "AND LanguageIx = $lIx";
      $rst = $db->query($q);
      if($r = $rst->fetch_array()){
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
      foreach($qs as $q)
        $db->query($q);
    }
    public function offsetsColumn($c, $tId, $study){
      $q = "SELECT COUNT(*) FROM Languages_$study";
      $r = $this->querySingleRow($q);
      return $this->offsetsFromCount(current($r));
    }
    public function pageColumn($c, $tId, $study, $offset){
      //Setup
      $ret = array();
      $tCols = $this->translateColumn($c);
      $description = $tCols['description'];
      $origCol = $tCols['origCol'];
      //Page query:
      $q = "SELECT $origCol, LanguageIx FROM Languages_$study LIMIT 30 OFFSET $offset";
      foreach($this->fetchRows($q) as $r){
        $q = "SELECT $c "
           . "FROM Page_DynamicTranslation_Languages "
           . "WHERE TranslationId = $tId "
           . "AND Study = '$study' "
           . "AND LanguageIx = ".$r[1];
        $translation = $this->querySingleRow($q);
        array_push($ret, array(
          'Description' => $description
        , 'Original'    => $r[0]
        , 'Translation' => array(
            'TranslationId'       => $tId
          , 'Translation'         => $translation[0]
          , 'Payload'             => implode(',', array($study, $r[1]))
          , 'TranslationProvider' => $this->getName()
          )
        ));
      }
      return $ret;
    }
    public function translateColumn($c){
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
      return array('description' => $description, 'origCol' => $origCol);
    }
    public function deleteTranslation($tId){
      $q = "DELETE FROM Page_DynamicTranslation_Languages WHERE TranslationId = $tId";
      $this->dbConnection->query($q);
    }
  }
?>
