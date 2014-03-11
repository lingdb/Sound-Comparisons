<?php
  /***/
  require_once "TranslationProvider.php";
  class StudyTitleTranslationProvider extends TranslationProvider{
    public function search($tId, $searchText){
      //Setup
      $ret = array();
      $description = $this->getDescription('dt_studyTitle_trans');
      //Search queries:
      $qs = array("SELECT StudyName, Trans, TranslationId "
          . "FROM Page_DynamicTranslation_StudyTitle "
          . "WHERE TranslationId = $tId "
          . "AND Trans LIKE '%$searchText%'");
      if($this->searchAllTranslations()){
        array_push($qs,
          "SELECT StudyName, Trans, 1 "
        . "FROM Page_DynamicTranslation_StudyTitle "
        . "WHERE TranslationId = 1 "
        . "AND Trans LIKE '%$searchText%'"
        );
      }
      foreach($this->runQueries($qs) as $r){
        $payload = $r[0];
        $match   = $r[1];
        $matchId = $r[2];
        $q = "SELECT Trans "
           . "FROM Page_DynamicTranslation_StudyTitle "
           . "WHERE TranslationId = 1 "
           . "AND StudyName = '$payload'";
        $original = $this->querySingleRow($q);
        $q = "SELECT Trans "
           . "FROM Page_DynamicTranslation_StudyTitle "
           . "WHERE TranslationId = $tId "
           . "AND StudyName = '$payload'";
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
    public function update($tId, $payload, $update){
      $db      = $this->dbConnection;
      $payload = $db->escape_string($payload);
      $update  = $db->escape_string($update);
      $qs = array(
        "DELETE FROM Page_DynamicTranslation_StudyTitle "
      . "WHERE TranslationId = $tId "
      . "AND StudyName = '$payload'"
      , "INSERT INTO Page_DynamicTranslation_StudyTitle "
      . "(TranslationId, Trans, StudyName) "
      . "VALUES ($tId, '$update', '$payload')"
      );
      foreach($qs as $q)
        $db->query($q);
    }
    public function offsets($tId, $study){
      $q = "SELECT COUNT(DISTINCT Name) FROM Studies";
      $r = $this->querySingleRow($q);
      return $this->offsetsFromCount(current($r));
    }
    public function page($tId, $study, $offset){
      //Setup
      $ret = array();
      $description = $this->getDescription('dt_studyTitle_trans');
      //Page query:
      $q = "SELECT StudyName, Trans "
         . "FROM Page_DynamicTranslation_StudyTitle "
         . "WHERE TranslationId = 1 LIMIT 30 OFFSET $offset";
      foreach($this->fetchRows($q) as $r){
        $sName = $r[0];
        $q = "SELECT Trans "
           . "FROM Page_DynamicTranslation_StudyTitle "
           . "WHERE TranslationId = $tId "
           . "AND StudyName = '$sName'";
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
    public function deleteTranslation($tId){
      $q = "DELETE FROM Page_DynamicTranslation_StudyTitle WHERE TranslationId = $tId";
      $this->dbConnection->query($q);
    }
  }
?>
