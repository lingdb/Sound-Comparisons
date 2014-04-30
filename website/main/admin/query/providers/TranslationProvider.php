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
      This method was originally an abstract one,
      but with the switch to the new table layout,
      we can have an implementation for all TranslationProviders.
    */
    public function update($tId, $payload, $update){
      $db       = $this->dbConnection;
      $tId      = $db->escape_string($tId);
      $payload  = $db->escape_string($payload);
      $update   = $db->escape_string($update);
      $category = $this->getName();
      $qs = array(
        "DELETE FROM Page_DynamicTranslation "
      . "WHERE TranslationId = $tId "
      . "AND Category = '$category' "
      . "AND Field = '$payload'"
      , "INSERT INTO Page_DynamicTranslation (TranslationId, Category, Field, Trans) "
      . "VALUES ($tId, '$category', '$payload', '$update')"
      );
      foreach($qs as $q)
        $db->query($q);
    }
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
    // HELPER FUNCTIONS BELOW
    /**
      A helper function to fetch all rows from a query.
    */
    public function fetchRows($set){
      if(is_string($set))
        $set = $this->dbConnection->query($set);
      $rows = array();
      while($r = $set->fetch_row())
        array_push($rows, $r);
      return $rows;
    }
    /**
      A helper function to execute multiple queries,
      and return all their results in a single array.
    */
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
    /**
      Translation field come with descriptions to aid work in the translation interface.
      This method fetches such descriptions.
    */
    public function getDescription($req){
      $q = "SELECT Description "
         . "FROM Page_StaticDescription "
         . "WHERE Req = '$req'";
      $rst = $this->dbConnection->query($q);
      if($r = $rst->fetch_row())
        return array('Req' => $req, 'Description' => $r[0]);
      return array('Req' => $req, 'Description' => 'Description not found in database.');
    }
    /**
      A simple helper method that fetches a single row form a query.
    */
    public function querySingleRow($q){
      return $this->dbConnection->query($q)->fetch_row();
    }
    /**
      A method to produce an array of offsets from a given count.
      offsets are generated in steps of 30,
      and this is required in the offsets methods of all TranslationProviders.
    */
    public function offsetsFromCount($count){
      $offsets = array();
      for($offset = 0; $offset < $count; $offset += 30){
        array_push($offsets, $offset);
      }
      return $offsets;
    }
    /**
      Information, wether all translations should be searched.
    */
    public function searchAllTranslations(){
      if(array_key_exists('searchAll', $_GET)){
        if($_GET['searchAll'] === 'true'){
          return true;
        }
      }
      return false;
    }
    /**
      Since all TranslationProviders have to search the Page_DynamicTranslation table,
      we can use this function to build a query for that cause at a central place.
      TODO I should be able to stop returning the $tId parameter.
    */
    protected function translationSearchQuery($tId, $searchText){
      $category = $this->getName();
      return "SELECT Field, Trans, $tId "
           . "FROM Page_DynamicTranslation "
           . "WHERE TranslationId = $tId "
           . "AND Category = '$category' "
           . "AND Trans LIKE '%$searchText%'";
    }
    /**
      All TranslationProviders get an easy way to fetch the Translation
      for a given field and translationId by this.
    */
    protected function getTranslationQuery($field, $tId){
      $category = $this->getName();
      return "SELECT Trans "
           . "FROM Page_DynamicTranslation "
           . "WHERE TranslationId = $tId "
           . "AND Category = '$category' "
           . "AND Field = '$field'";
    }
    /**
      This method handles the migration from the old translation schema.
      It will be around for some time,
      until I'm confident that the old layout is no longer in use.
    */
    public abstract function migrate();
  }
?>
