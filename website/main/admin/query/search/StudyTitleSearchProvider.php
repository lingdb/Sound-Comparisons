<?php
  /***/
  require_once "SearchProvider.php";
  class StudyTitleSearchProvider extends SearchProvider{
    public function search($tId, $searchText){
      //Setup
      $ret = array();
      $description = $this->getDescription('dt_studyTitle_trans');
      //Search queries:
      $qs = array(
        "SELECT StudyName, Trans "
      . "FROM Page_DynamicTranslation_StudyTitle "
      . "WHERE TranslationId = $tId "
      . "AND Trans LIKE '%$searchText%'"
      , "SELECT StudyName, Trans "
      . "FROM Page_DynamicTranslation_StudyTitle "
      . "WHERE TranslationId = 1 "
      . "AND Trans LIKE '%$searchText%'"
      );
      foreach($this->runQueries($qs) as $r){
        $payload = $r[0];
        $match   = $r[1];
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
        , 'Original'    => $original[0]
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
      $payload = mysql_real_escape_string($payload);
      $update  = mysql_real_escape_string($update);
      $qs = array(
        "DELETE FROM Page_DynamicTranslation_StudyTitle "
      . "WHERE TranslationId = $tId "
      . "AND StudyName = '$payload'"
      , "INSERT INTO Page_DynamicTranslation_StudyTitle "
      . "(TranslationId, Trans, StudyName) "
      . "VALUES ($tId, '$update', '$payload')"
      );
      foreach($qs as $q)
        mysql_query($q, $this->dbConnection);
    }
  }
?>
