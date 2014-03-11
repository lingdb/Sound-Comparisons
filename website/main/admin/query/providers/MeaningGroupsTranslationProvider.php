<?php
  /***/
  require_once "TranslationProvider.php";
  class MeaningGroupsTranslationProvider extends TranslationProvider{
    public function search($tId, $searchText){
      //Setup:
      $ret = array();
      $description = $this->getDescription('dt_meaningGroups_trans');
      //Search queries:
      $qs = array("SELECT MeaningGroupIx, Trans, TranslationId "
          . "FROM Page_DynamicTranslation_MeaningGroups "
          . "WHERE TranslationId = $tId "
          . "AND Trans LIKE '%$searchText%'");
      if($this->searchAllTranslations()){
        array_push($qs,
          "SELECT MeaningGroupIx, Name, 1 "
        . "FROM MeaningGroups "
        . "WHERE Name LIKE '%$searchText%'"
        );
      }
      foreach($this->runQueries($qs) as $r){
        $payload = $r[0];
        $match   = $r[1];
        $matchId = $r[2];
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
        "DELETE FROM Page_DynamicTranslation_MeaningGroups "
      . "WHERE TranslationId = $tId "
      . "AND MeaningGroupIx = $payload"
      , "INSERT INTO Page_DynamicTranslation_MeaningGroups "
      . "(TranslationId, Trans, MeaningGroupIx) "
      . "VALUES ($tId, '$update', $payload)"
      );
      foreach($qs as $q)
        $db->query($q);
    }
    public function offsets($tId, $study){
      $q = "SELECT COUNT(*) FROM MeaningGroups";
      $r = $this->querySingleRow($q);
      return $this->offsetsFromCount(current($r));
    }
    public function page($tId, $study, $offset){
      //Setup:
      $ret = array();
      $description = $this->getDescription('dt_meaningGroups_trans');
      //Page query:
      $q = "SELECT MeaningGroupIx, Name "
         . "FROM MeaningGroups LIMIT 30 OFFSET $offset";
      foreach($this->fetchRows($q) as $r){
        $q = "SELECT Trans "
           . "FROM Page_DynamicTranslation_MeaningGroups "
           . "WHERE TranslationId = $tId "
           . "AND MeaningGroupIx = ".$r[0];
        $translation = $this->querySingleRow($q);
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
      $q = "DELETE FROM Page_DynamicTranslation_MeaningGroups WHERE TranslationId = $tId";
      $this->dbConnection->query($q);
    }
  }
?>
