<?php
  /***/
  require_once "TranslationProvider.php";
  /*
    Mapping between tables Languages_$s, Page_DynamicTranslation:
    CONCAT(Study,'-',LanguageIx) <-> Field
    $c (column)                  <-> Trans
  */
  class SpellingLanguagesTranslationProvider extends TranslationProvider{
    /***/
    public function search($tId, $searchText, $searchAll = false){
      //Setup
      $ret = array();
      $description = TranslationProvider::getDescription('dt_languages_specificLanguageVarietyName');
      //Search queries:
      $qs = array($this->translationSearchQuery($tId, $searchText));
      if($searchAll){
        $q   = 'SELECT Name FROM Studies';
        $set = $this->dbConnection->query($q);
        foreach($this->fetchRows($set) as $s){
          $s = $s[0];
          $q = "SELECT CONCAT('$s-', LanguageIx), SpellingRfcLangName, 1 FROM Languages_$s "
             . "WHERE SpellingRfcLangName LIKE '%$searchText%'";
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
        $q = "SELECT SpellingRfcLangName "
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
    /***/
    public function offsets($tId, $study){
      $q = "SELECT COUNT(*) FROM Languages_$study "
         . "WHERE SpellingRfcLangName != '' AND SpellingRfcLangName IS NOT NULL";
      $r = $this->querySingleRow($q);
      return $this->offsetsFromCount(current($r));
    }
    /***/
    public function page($tId, $study, $offset){
      //Setup
      $ret         = array();
      $description = TranslationProvider::getDescription('dt_languages_specificLanguageVarietyName');
      //Page query:
      $o = ($offset == -1) ? '' : " LIMIT 30 OFFSET $offset";
      $q = "SELECT SpellingRfcLangName, LanguageIx FROM Languages_$study "
         . "WHERE SpellingRfcLangName != '' AND SpellingRfcLangName IS NOT NULL$o";
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
    /**
      The SpellingLanguagesTranslationProvider was originally an instance
      of the LanguagesTranslationProvider, which extended DynamicTranslationProvider.
      To stay compatibile, the old name is kept.
    */
    public function getName(){
      return 'LanguagesTranslationProvider-Languages_-Trans_SpellingRfcLangName';
    }
  }
?>
