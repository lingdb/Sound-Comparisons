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
      $set = mysql_query($q, $this->dbConnection);
      while($r = mysql_fetch_row($set)){
        $payload = $r[0];
        $match   = $r[1];
        $description = $this->getDescription($payload);
        $q = "SELECT Trans FROM Page_StaticTranslation "
           . "WHERE TranslationId = 1 AND Req = '$payload'";
        $original = mysql_fetch_row(mysql_query($q, $this->dbConnection));
        $q = "SELECT Trans FROM Page_StaticTranslation "
           . "WHERE TranslationId = $tId AND Req = '$payload'";
        $translation = mysql_fetch_row(mysql_query($q, $this->dbConnection));
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
      $q = "DELETE FROM Page_StaticTranslation WHERE Req = '$payload' AND TranslationId = $tId";
      mysql_query($q, $this->dbConnection);
      $q = "INSERT INTO Page_StaticTranslation(TranslationId, Req, Trans) VALUES ($tId, '$payload', '$update')";
      mysql_query($q, $this->dbConnection);
    }
  }
?>
