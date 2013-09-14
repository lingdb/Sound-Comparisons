<?php
  /***/
  require_once "SearchProvider.php";
  class StudySearchProvider extends SearchProvider{
    public function search($tId, $searchText){
      //Setup:
      $ret = array();
      $description = $this->getDescription('dt_studies_trans');
      //Search queries:
      $qs = array(
        "SELECT Study, Trans "
      . "FROM Page_DynamicTranslation_Studies "
      . "WHERE Trans LIKE '%$searchText%' "
      . "AND TranslationId = $tId"
      , "SELECT Name, Name "
      . "FROM Studies "
      . "WHERE Name LIKE '%$searchText%'"
      );
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
            'TranslationId'  => $tId
          , 'Translation'    => $translation[0]
          , 'Payload'        => $payload
          , 'SearchProvider' => $this->getName()
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
  }
?>
