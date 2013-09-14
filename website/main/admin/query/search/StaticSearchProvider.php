<?php
  /**
    The StaticSearchProvider provides search and update
    facilities for the static translation.
  */
  require_once "SearchProvider.php";
  class StaticSearchProvider extends SearchProvider{
    public function search($tId, $searchText){
      $ret = array();
      $q = "SELECT Req, Trans "
         . "FROM Page_StaticTranslation "
         . "WHERE Trans LIKE '%$searchText%'";
      $set = $this->dbConnection->query($q);
      while($r = $set->fetch_row()){
        $payload = $r[0];
        $match   = $r[1];
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
      $q = "DELETE FROM Page_StaticTranslation WHERE Req = '$payload' AND TranslationId = $tId";
      $db->query($q);
      $q = "INSERT INTO Page_StaticTranslation(TranslationId, Req, Trans) VALUES ($tId, '$payload', '$update')";
      $db->query($q);
    }
  }
?>
