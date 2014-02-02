<?php
  /*
    Each field that can be translated will deliver it's own TranslationProvider.
    The TranslationProvider can be identified by a name.
    It has a method to search for a given SearchText with a TranslationId.
  */
  abstract class TranslationProvider{
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
      , Translation: {TranslationId: 5, Translation: '', Payload: '', TranslationProvider: ''}
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
    /**
      The methods offsets and page are build to implement paging via search.
      I've made the experience, that the way the TranslationBySearch Feature
      handles the data is more sophisticated than what DynamicTranslation currently does.
      Therefore DT will use the facilities of TBS and TBS needs paging for it.
      The offsets method shall build and array of offsets that can be used for paging
      given a study, a tId and a limit=30.
      @param tId TranslationId to use
      @param study the Study for which we want to translate
      @return Int[] offsets
    */
    public abstract function offsets($tId, $study);
    /**
      @param tId TranslationId to use
      @param study the Study which we want to translate
      @param offset the offset to use together with a limit=30
      Just like search this will produce an array of arrays.
      The inner Arrays will resemble JSON objects
      with the following syntax:
      {
        Description: {Req: '', Description: ''}
      , Original: ''
      , Translation: {TranslationId: 5, Translation: '', Payload: '', TranslationProvider: ''}
      }
      The Description consists of Description.Description and Req,
      Description.Description is the real text,
      and Description.Req is necessary to edit the description.
      In contrast to search, page doesn't deliver a Match field.
      Original is the English version of the content.
      Translation.Translation is the current Translation in the given TranslationId.
      The data in Translation is necessary to edit a Translation.
    */
    public abstract function page($tId, $study, $offset);
    /**
      @param tId TranslationId to get rid of
      Deletes all entries for a given TranslationId.
      This method must not be column dependent,
      and will be executed only for each class once
      instead of each Provider instance.
    */
    public abstract function deleteTranslation($tId);
    // HELPER FUNCTIONS BELOW
    /***/
    public function fetchRows($set){
      if(is_string($set))
        $set = $this->dbConnection->query($set);
      $rows = array();
      while($r = $set->fetch_row())
        array_push($rows, $r);
      return $rows;
    }
    /***/
    public function runQueries($qs){
      if(is_string($qs))
        $qs = array($qs);
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
    /***/
    public function offsetsFromCount($count){
      $offsets = array();
      for($offset = 0; $offset < $count; $offset += 30){
        array_push($offsets, $offset);
      }
      return $offsets;
    }
  }
?>
