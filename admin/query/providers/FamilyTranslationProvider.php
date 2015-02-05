<?php
  /**
    The FamilyTranslationProvider provides search and update
    facilities for the table Page_DynamicTranslation_Families.
  */
  require_once "TranslationProvider.php";
  /*
    Mapping between tables Families, Page_DynamicTranslation:
    CONCAT(StudyIx, FamilyIx) <-> Field
    FamilyNm                  <-> Trans
  */
  class FamilyTranslationProvider extends TranslationProvider{
    public function search($tId, $searchText, $searchAll = false){
      //Setup:
      $ret = array();
      $description = $this->getDescription('dt_families_trans');
      //Search queries:
      $qs = array($this->translationSearchQuery($tId, $searchText));
      if($searchAll){
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
        $q = $this->getTranslationQuery($payload, $tId);
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
      $o = ($offset == -1) ? '' : " LIMIT 30 OFFSET $offset";
      $q = "SELECT CONCAT(StudyIx, FamilyIx), FamilyNm "
         . "FROM Families$o";
      foreach($this->fetchRows($q) as $r){
        $q = $this->getTranslationQuery($r[0], $tId);
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
  }
?>
