<?php
  /**
    The StaticTranslationProvider provides search and update
    facilities for the static translation.
  */
  require_once "TranslationProvider.php";
  class StaticTranslationProvider extends TranslationProvider{
    public function migrate(){
      //Static Translation doesn't need migration.
    }
    public function search($tId, $searchText){
      $ret = array();
      $q = "SELECT Req, Trans, TranslationId FROM Page_StaticTranslation "
         . "WHERE Trans LIKE '%$searchText%'";
      $set = $this->dbConnection->query($q);
      while($r = $set->fetch_row()){
        $payload = $r[0];
        $match   = $r[1];
        $matchId = $r[2];
        $description = $this->getDescription($payload);
        $q = "SELECT Trans FROM Page_StaticTranslation "
           . "WHERE TranslationId = 1 AND Req = '$payload'";
        $original = $this->dbConnection->query($q)->fetch_row();
        $q = "SELECT Trans FROM Page_StaticTranslation "
           . "WHERE TranslationId = $tId AND Req = '$payload'";
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
      $q = "DELETE FROM Page_StaticTranslation WHERE Req = '$payload' AND TranslationId = $tId";
      $db->query($q);
      $q = "INSERT INTO Page_StaticTranslation(TranslationId, Req, Trans) VALUES ($tId, '$payload', '$update')";
      $db->query($q);
    }
    public function offsets($tId, $study){
      $q = "SELECT COUNT(*) FROM Page_StaticTranslation WHERE TranslationId = 1";
      $r = $this->querySingleRow($q);
      return $this->offsetsFromCount(current($r));
    }
    public function page($tId, $study, $offset){
      $ret = array();
      $q = "SELECT Req, Trans FROM Page_StaticTranslation "
         . "WHERE TranslationId = 1 LIMIT 30 OFFSET $offset";
      foreach($this->fetchRows($q) as $r){
        $payload = $r[0];
        $description = $this->getDescription($payload);
        $q = "SELECT Trans FROM Page_StaticTranslation "
           . "WHERE TranslationId = $tId AND Req = '$payload'";
        $translation = $this->dbConnection->query($q)->fetch_row();
        array_push($ret, array(
          'Description' => $description
        , 'Original'    => $r[1]
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
  }
?>
