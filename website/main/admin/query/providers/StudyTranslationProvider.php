<?php
  /***/
  require_once "TranslationProvider.php";
  class StudyTranslationProvider extends TranslationProvider{
    public function migrate(){
      $category = $this->getName();
      $q = "INSERT INTO Page_DynamicTranslation (TranslationId, Category, Field, Trans) "
         . "SELECT TranslationId, '$category', Study, Trans "
         . "FROM Page_DynamicTranslation_Studies";
      $this->dbConnection->query($q);
    }
    public function search($tId, $searchText){
      //Setup:
      $ret = array();
      $description = $this->getDescription('dt_studies_trans');
      //Search queries:
      $qs = array($this->translationSearchQuery($tId, $searchText));
      if($this->searchAllTranslations()){
        array_push($qs,
          "SELECT Name, Name, 1 FROM Studies "
        . "WHERE Name LIKE '%$searchText%'"
        );
      }
      foreach($this->runQueries($qs) as $r){
        $payload = $r[0]; // Also the original :)
        $match   = $r[1];
        $matchId = $r[2];
        $q = $this->getTranslationQuery($payload, $tId);
        $translation = $this->querySingleRow($q);
        array_push($ret, array(
          'Description' => $description
        , 'Match'       => $match
        , 'MatchId'     => $matchId
        , 'Original'    => $payload
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
      //Setup:
      $ret = array();
      $description = $this->getDescription('dt_studies_trans');
      //Page query:
      $q = "SELECT DISTINCT Name FROM Studies LIMIT 30 OFFSET $offset";
      foreach($this->fetchRows($q) as $r){
        $payload = $r[0]; // Also the original :)
        $q = $this->getTranslationQuery($payload, $tId);
        $translation = $this->querySingleRow($q);
        array_push($ret, array(
          'Description' => $description
        , 'Original'    => $payload
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
  }
?>
