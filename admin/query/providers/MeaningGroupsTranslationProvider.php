<?php
  /***/
  require_once "TranslationProvider.php";
  /*
    Mapping between tables MeaningGroups, Page_DynamicTranslation:
    MeaningGroupIx <-> Field
    Name           <-> Trans
  */
  class MeaningGroupsTranslationProvider extends TranslationProvider{
    public function search($tId, $searchText){
      //Setup:
      $ret = array();
      $description = $this->getDescription('dt_meaningGroups_trans');
      //Search queries:
      $qs = array($this->translationSearchQuery($tId, $searchText));
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
        $q = $this->getTranslationQuery($r[0], $tId);
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
  }
?>
