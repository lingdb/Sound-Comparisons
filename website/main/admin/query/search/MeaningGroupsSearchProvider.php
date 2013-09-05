<?php
  /***/
  require_once "SearchProvider.php";
  class MeaningGroupsSearchProvider extends SearchProvider{
    public function search($tId, $searchText){
      //Setup:
      $ret = array();
      $description = $this->getDescription('dt_meaningGroups_trans');
      //Search queries:
      $qs = array(
        "SELECT MeaningGroupIx, Trans "
      . "FROM Page_DynamicTranslation_MeaningGroups "
      . "WHERE TranslationId = $tId "
      . "AND Trans LIKE '%$searchText%'"
      , "SELECT MeaningGroupIx, Name "
      . "FROM MeaningGroups "
      . "WHERE Name LIKE '%$searchText%'"
      );
      foreach($this->runQueries($qs) as $r){
        $payload = $r[0];
        $match   = $r[1];
        $q = "SELECT Name "
           . "FROM MeaningGroups "
           . "WHERE MeaningGroupIx = $payload";
        $original = $this->querySingleRow($q);
        $q = "SELECT Trans "
           . "FROM Page_DynamicTranslation_MeaningGroups "
           . "WHERE TranslationId = $tId "
           . "AND MeaningGroupIx = $payload";
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
        "DELETE FROM Page_DynamicTranslation_MeaningGroups "
      . "WHERE TranslationId = $tId "
      . "AND MeaningGroupIx = $payload"
      , "INSERT INTO Page_DynamicTranslation_MeaningGroups "
      . "(TranslationId, Trans, MeaningGroupIx) "
      . "VALUES ($tId, '$update', $payload)"
      );
      foreach($qs as $q)
        mysql_query($q, $this->dbConnection);
    }
  }
?>
