<?php
  /**
    The FamilySearchProvider provides search and update
    facilities for the table Page_DynamicTranslation_Families.
  */
  require_once "SearchProvider.php";
  class FamilySearchProvider extends SearchProvider{
    public function search($tId, $searchText){
      //Setup:
      $ret = array();
      $description = $this->getDescription('dt_families_trans');
      //Search queries:
      $qs  = array(
        "SELECT CONCAT(StudyIx, FamilyIx), Trans "
      . "FROM Page_DynamicTranslation_Families "
      . "WHERE Trans LIKE '%$searchText%'"
      , "SELECT CONCAT(StudyIx, FamilyIx), FamilyNm "
      . "FROM Families "
      . "WHERE FamilyNm LIKE '%$searchText%'"
      );
      foreach($this->runQueries($qs) as $r){
        $payload = $r[0];
        $match   = $r[1];
        $q = "SELECT FamilyNm "
           . "FROM Families "
           . "WHERE CONCAT(StudyIx, FamilyIx) = $payload";
        $original = $this->dbConnection->query($q)->fetch_row();
        $q = "SELECT Trans "
           . "FROM Page_DynamicTranslation_Families "
           . "WHERE TranslationId = $tId "
           . "AND CONCAT(StudyIx, FamilyIx) = $payload";
        $translation = $this->dbConnection->query($q)->fetch_row();
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
      $db      = $this->dbConnection;
      $payload = $db->escape_string($payload);
      $update  = $db->escape_string($update);
      $qs = array(
        "DELETE FROM Page_DynamicTranslation_Families "
      . "WHERE CONCAT(StudyIx, FamilyIx) = $payload AND TranslationId = $tId"
      , "INSERT INTO Page_DynamicTranslation_Families(TranslationId, StudyIx, FamilyIx, Trans) "
      . "SELECT $tId, StudyIx, FamilyIx, '$update' FROM Families "
      . "WHERE CONCAT(StudyIx, FamilyIx) = $payload"
      );
      foreach($qs as $q)
        $db->query($q);
    }
  }
?>
