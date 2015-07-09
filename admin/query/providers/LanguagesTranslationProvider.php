<?php
  /***/
  require_once "DynamicTranslationProvider.php";
  /*
    Mapping between tables Languages_$s, Page_DynamicTranslation:
    CONCAT(Study,'-',LanguageIx) <-> Field
    $c (column)                  <-> Trans
  */
  class LanguagesTranslationProvider extends DynamicTranslationProvider{
    public function getTable(){return 'Languages_';}
    public function searchColumn($c, $tId, $searchText, $searchAll = false){
      //Setup
      $ret = array();
      $tCols = $this->translateColumn($c);
      $description = $tCols['description'];
      $origCol = $tCols['origCol'];
      //Search queries:
      $qs = array($this->translationSearchQuery($tId, $searchText));
      if($searchAll){
        $q   = 'SELECT Name FROM Studies';
        $set = $this->dbConnection->query($q);
        foreach($this->fetchRows($set) as $s){
          $s = $s[0];
          $q = "SELECT CONCAT('$s-', LanguageIx), $origCol, 1"
             . "FROM Languages_$s "
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
        $lIx     = $parts[1];
        $q = "SELECT $origCol "
           . "FROM Languages_$study "
           . "WHERE LanguageIx = $lIx";
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
          , 'Payload'             => $payload
          , 'TranslationProvider' => $this->getName()
          )
        ));
      }
      return $ret;
    }
    public function offsetsColumn($c, $tId, $study){
      $q = "SELECT COUNT(*) FROM Languages_$study";
      $r = $this->querySingleRow($q);
      return $this->offsetsFromCount(current($r));
    }
    public function pageColumn($c, $tId, $study, $offset){
      //Setup
      $ret         = array();
      $tCols       = $this->translateColumn($c);
      $description = $tCols['description'];
      $origCol     = $tCols['origCol'];
      //Page query:
      $o = ($offset == -1) ? '' : " LIMIT 30 OFFSET $offset";
      $q = "SELECT $origCol, LanguageIx FROM Languages_$study$o";
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
        case 'Trans_ShortName':
          $description = TranslationProvider::getDescription('dt_languages_shortName');
          $origCol = 'ShortName';
        break;
        case 'Trans_SpellingRfcLangName':
          $description = TranslationProvider::getDescription('dt_languages_specificLanguageVarietyName');
          $origCol = 'SpellingRfcLangName';
        break;
        case 'Trans_SpecificLanguageVarietyName':
          $description = TranslationProvider::getDescription('dt_languages_spellingRfcLangName');
          $origCol = 'SpecificLanguageVarietyName';
        break;
      }
      return array('description' => $description, 'origCol' => $origCol);
    }
  }
