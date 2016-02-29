<?php
  /***/
  require_once('TranslationProvider.php');
  class StudyTitleTranslationProvider extends TranslationProvider{
    public function search($tId, $searchText, $searchAll = false){
      //Setup
      $ret = array();
      $description = TranslationProvider::getDescription('dt_studyTitle_trans');
      //Search queries:
      $qs = array($this->translationSearchQuery($tId, $searchText));
      if($searchAll){
        array_push($qs, $this->translationSearchQuery(1, $searchText));
      }
      foreach($this->runQueries($qs) as $r){
        $payload = $r[0];
        $match   = $r[1];
        $matchId = $r[2];
        $q = $this->getTranslationQuery($payload, 1);
        $original = $this->querySingleRow($q);
        $q = $this->getTranslationQuery($payload, $tId);
        $translation = $this->querySingleRow($q);
        array_push($ret, array(
          'Description' => $description
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
    public function offsets($tId, $study){
      $q = "SELECT COUNT(DISTINCT Name) FROM Studies";
      $r = $this->querySingleRow($q);
      return $this->offsetsFromCount(current($r));
    }
    public function page($tId, $study, $offset){
      //Setup
      $ret = array();
      $description = TranslationProvider::getDescription('dt_studyTitle_trans');
      $category = $this->getName();
      //Page query:
      $o = ($offset == -1) ? '' : " LIMIT 30 OFFSET $offset";
      $q = "SELECT Name, Name FROM Studies$o";
      foreach($this->fetchRows($q) as $r){
        $sName = $r[0];
        $q = $this->getTranslationQuery($sName, $tId);
        $translation = $this->querySingleRow($q);
        array_push($ret, array(
          'Description' => $description
        , 'Original'    => $r[1]
        , 'Translation' => array(
            'TranslationId'       => $tId
          , 'Translation'         => $translation[0]
          , 'Payload'             => $sName
          , 'TranslationProvider' => $this->getName()
          )
        ));
      }
      return $ret;
    }
  }
