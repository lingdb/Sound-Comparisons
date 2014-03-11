<?php
  /**
    The FamilyTranslationProvider provides search and update
    facilities for the table Page_DynamicTranslation_Families.
  */
  require_once "TranslationProvider.php";
  class FamilyTranslationProvider extends TranslationProvider{
    public function search($tId, $searchText){
      //Setup:
      $ret = array();
      $description = $this->getDescription('dt_families_trans');
      //Search queries:
      $qs = array('SELECT CONCAT(StudyIx, FamilyIx), Trans, TranslationId '
          . 'FROM Page_DynamicTranslation_Families '
          . "WHERE Trans LIKE '%$searchText%' AND TranslationId = $tId");
      if($this->searchAllTranslations()){
        array_push($qs,
          'SELECT CONCAT(StudyIx, FamilyIx), FamilyNm, 1 '
        . "FROM Families WHERE FamilyNm LIKE '%$searchText%'");
      }
      foreach($this->runQueries($qs) as $r){
        $payload = $r[0];
        $match   = $r[1];
        $matchId = $r[2];
        $q = 'SELECT FamilyNm FROM Families '
           . "WHERE CONCAT(StudyIx, FamilyIx) = $payload";
        $original = $this->dbConnection->query($q)->fetch_row();
        $q = 'SELECT Trans FROM Page_DynamicTranslation_Families '
           . "WHERE TranslationId = $tId "
           . "AND CONCAT(StudyIx, FamilyIx) = $payload";
        $translation = $this->dbConnection->query($q)->fetch_row();
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
        "DELETE FROM Page_DynamicTranslation_Families "
      . "WHERE CONCAT(StudyIx, FamilyIx) = $payload AND TranslationId = $tId"
      , "INSERT INTO Page_DynamicTranslation_Families(TranslationId, StudyIx, FamilyIx, Trans) "
      . "SELECT $tId, StudyIx, FamilyIx, '$update' FROM Families "
      . "WHERE CONCAT(StudyIx, FamilyIx) = $payload"
      );
      foreach($qs as $q)
        $db->query($q);
    }
    public function offsets($tId, $study){
      $q = "SELECT COUNT(*) FROM Families";
      $r = $this->querySingleRow($q);
      return $this->offsetsFromCount(current($r));
    }
    public function page($tId, $study, $offset){
      //Setup:
      $ret = array();
      $description = $this->getDescription('dt_families_trans');
      //Page query:
      $q = "SELECT CONCAT(StudyIx, FamilyIx), FamilyNm "
         . "FROM Families LIMIT 30 OFFSET $offset";
      foreach($this->fetchRows($q) as $r){
        $q = "SELECT Trans "
           . "FROM Page_DynamicTranslation_Families "
           . "WHERE TranslationId = $tId "
           . "AND CONCAT(StudyIx, FamilyIx) = ".$r[0];
        $translation = $this->dbConnection->query($q)->fetch_row();
        array_push($ret, array(
          'Description' => $description
        , 'Original'    => $r[1]
        , 'Translation' => array(
            'TranslationId'       => $tId
          , 'Translation'         => $translation[0]
          , 'Payload'             => $r[0]
          , 'TranslationProvider' => $this->getName()
          )
        ));
      }
      return $ret;
    }
    public function deleteTranslation($tId){
      $q = "DELETE FROM Page_DynamicTranslation_Families WHERE TranslationId = $tId";
      $this->dbConnection->query($q);
    }
  }
?>
