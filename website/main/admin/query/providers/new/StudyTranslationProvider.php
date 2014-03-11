<?php
  /***/
  require_once "TranslationProvider.php";
  class StudyTranslationProvider extends TranslationProvider{
    public function search($tId, $searchText){
      //Setup:
      $ret = array();
      $description = $this->getDescription('dt_studies_trans');
      //Search queries:
      $qs = array("SELECT Study, Trans "
          . "FROM Page_DynamicTranslation_Studies "
          . "WHERE Trans LIKE '%$searchText%' "
          . "AND TranslationId = $tId");
      if($this->searchAllTranslations()){
        array_push($qs,
          "SELECT Name, Name FROM Studies "
        . "WHERE Name LIKE '%$searchText%'"
        );
      }
      foreach($this->runQueries($qs) as $r){
        $payload = $r[0]; // Also the original :)
        $match   = $r[1];
        $q = "SELECT Trans "
           . "FROM Page_DynamicTranslation_Studies "
           . "WHERE Study = '$payload' "
           . "AND TranslationId = $tId";
        $translation = $this->querySingleRow($q);
        array_push($ret, array(
          'Description' => $description
        , 'Match'       => $match
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
    public function update($tId, $payload, $update){
      $db      = $this->dbConnection;
      $payload = $db->escape_string($payload);
      $update  = $db->escape_string($update);
      $qs = array(
        "DELETE FROM Page_DynamicTranslation_Studies "
      . "WHERE TranslationId = $tId "
      . "AND Study = '$payload'"
      , "INSERT INTO Page_DynamicTranslation_Studies "
      . "(TranslationId, Study, Trans) "
      . "VALUES ($tId, '$payload', '$update')"
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
      //Setup:
      $ret = array();
      $description = $this->getDescription('dt_studies_trans');
      //Page query:
      $q = "SELECT DISTINCT Name FROM Studies LIMIT 30 OFFSET $offset";
      foreach($this->fetchRows($q) as $r){
        $payload = $r[0]; // Also the original :)
        $q = "SELECT Trans "
           . "FROM Page_DynamicTranslation_Studies "
           . "WHERE Study = '$payload' "
           . "AND TranslationId = $tId";
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
    public function deleteTranslation($tId){
      $q = "DELETE FROM Page_DynamicTranslation_Studies WHERE TranslationId = $tId";
      $this->dbConnection->query($q);
    }
  }
?>
