<?php
  /*
    Each field that can be translated will deliver it's own SearchProvider.
    The SearchProvider can be identified by a name.
    It has a method to search for a given SearchText with a TranslationId.
  */
  abstract class SearchProvider{
    public $dbConnection;
    public function __construct($dbConnection){
      $this->dbConnection = $dbConnection;
    }
    public function getName(){
      return get_class($this);
    }
    /**
      This will produce an array of arrays.
      The inner Arrays will resemble JSON objects
      with the following syntax:
      {
        Description: {Req: '', Description: ''}
      , Match: ''
      , Original: ''
      , Translation: {TranslationId: 5, Translation: '', Payload: '', SearchProvider: ''}
      }
      The Description consists of Description.Description and Req,
      Description.Description is the real text,
      and Description.Req is necessary to edit the description.
      Match is the text that matched the search.
      Original is the English version of the content.
      Translation.Translation is the current Translation in the given TranslationId.
      The data in Translation is necessary to edit a Translation.
    */
    public abstract function search($tId, $searchText);
    /**
      This will save the given update as the new translation with the help
      of a translationId and a payload.
    */
    public abstract function update($tId, $payload, $update);
    // HELPER FUNCTIONS BELOW
    /***/
    public function fetchRows($set){
      $rows = array();
      while($r = $set->fetch_row())
        array_push($rows, $r);
      return $rows;
    }
    /***/
    public function runQueries($qs){
      $rows = array();
      foreach($qs as $q){
        $set  = $this->dbConnection->query($q);
        $rs   = $this->fetchRows($set);
        $rows = array_merge($rows, $rs);
      }
      return $rows;
    }
    /***/
    public function getDescription($req){
      $q = "SELECT Description "
         . "FROM Page_StaticDescription "
         . "WHERE Req = '$req'";
      $rst = $this->dbConnection->query($q);
      if($r = $rst->fetch_row())
        return array('Req' => $req, 'Description' => $r[0]);
      return array('Req' => $req, 'Description' => 'Description not found in database.');
    }
    /***/
    public function querySingleRow($q){
      return $this->dbConnection->query($q)->fetch_row();
    }
  }
?>
