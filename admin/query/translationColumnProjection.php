<?php
require_once('translationTableProjection.php');
/**
  Some things we want to do with the TranslationTableDescription
  are only possible once we use a single column.
  This class extends TranslationTableProjection and reuses $descriptions.
*/
class TranslationColumnProjection extends TranslationTableProjection {
  /**
    @param $lambda function($column)
    @return $lambda() || Exception
    Executes given $lambda with $column if possible,
    and returns an Exception otherwise.
  */
  private function withColumn($lambda){
    //Check if projection is on single column:
    if(count($this->descriptions) !== 1){
      return new Exception('Projection on other than a single table in TranslationTableProjection.update()');
    }
    foreach($this->descriptions as $tableName => $desc){
      if(count($desc['columns']) !== 1){
        return new Exception('Projection on other than a single column in TranslationTableProjection.update()');
      }
      foreach($desc['columns'] as $column){
        return $lambda($column);
      }
    }
    //Die because something is wrong:
    Config::error('Unreachable code?!', true, true);
  }
  /**
    @return $tableName String
  */
  public function getTable(){
    return current($this->withTables(function($tName){
      return $tName;
    }));
  }
  /**
    @return $study String || null
    Null is returned if dependsOnStudy !== true.
  */
  public function getStudy(){
    $studies = $this->withTables(function($tName, $desc){
      if($desc['dependsOnStudy'] === true){
        return $desc['study'];
      }
      return null;
    });
    foreach($studies as $study){
      if($study !== null){
        return $study;
      }
    }
    return null;
  }
  /**
    @return $description array('Req' => String, 'Description' => String) || Exception
    Fetches the Req and Description fields for a TranslationColumnProjection.
  */
  public function getDescription(){
    return $this->withColumn(function($column){
      return TranslationTableProjection::fetchDescription($column);
    });
  }
  /**
    @param $tId TranslationId
    @param $payload String fieldSelect value from description
    @param $update String new value for translation entry
    @return true || Exception
    Build after the preimage of TranslationProvider.update(…)
    This method updates the translation for a TranslationTableProjection on a single column.
    Should the projection have more than a single column,
    an Exception will be returned.
    All given parameters will be escaped by this method.
  */
  public function update($tId, $payload, $update){
    return $this->withColumn(function($column) use ($tId, $payload, $update){
      //Sanitize input:
      $db       = Config::getConnection();
      $tId      = $db->escape_string($tId);
      $payload  = $db->escape_string($payload);
      $update   = $db->escape_string($update);
      $category = $column['category'];
      $qs = array(
        "DELETE FROM Page_DynamicTranslation "
      . "WHERE TranslationId = $tId "
      . "AND Category = '$category' "
      . "AND Field = '$payload'"
      , "INSERT INTO Page_DynamicTranslation (TranslationId, Category, Field, Trans) "
      . "VALUES ($tId, '$category', '$payload', '$update')"
      , "UPDATE Page_Translations SET lastChangeDynamic = CURRENT_TIMESTAMP() WHERE TranslationId = $tId"
      );
      foreach($qs as $q){
        $db->query($q);
      }
      return true;
    });
  }
  /**
    @param $limit Int = 30
    @return $offsets [Int]
    This method calculates the offsets that could be used for pagination with a TranslationColumnProjection, provided a given $limit.
  */
  public function offsets($limit = 30){
    $table = $this->getTable();
    $q = "SELECT COUNT(*) FROM $table";
    $set = Config::getConnection()->query($q);
    $count = 0;
    if($r = $set->fetch_assoc()){
      $count = $r[0];
    }
    $offsets = array();
    for($offset = 0; $offset < $count; $offset += $limit){
      array_push($offsets, $offset);
    }
    return $offsets;
  }
  /**
    @param $entry obj
    @return $entry obj
    obj will be an array resembling a JSON object following this syntax:
    {
      Description: {Req: '', Description: ''}
    , Original: ''
    , Translation: {TranslationId: 5, Translation: '', Payload: '', TranslationProvider: ''}
    }
    Adds/Overwrites the Translation.Translation field if possible.
  */
  protected function addTranslation($entry){
    $trans       = $entry['Translation'];
    $tId         = $trans['TranslationId'];
    $category    = $trans['TranslationProvider'];
    $fieldSelect = $trans['Payload'];
    //Trying to fetch existing translation:
    $q = "SELECT Trans FROM Page_DynamicTranslation "
       . "WHERE TranslationId = $tId "
       . "AND Category = '$category' "
       . "AND Field = '$fieldSelect' "
       . "LIMIT 1";
    foreach(DataProvider::fetchAll($q) as $r){//foreach works as if
      $entry['Translation']['Translation'] = $r['Trans'];
    }
    return $entry;
  }
  /**
    @param $entry obj
    @return $entry obj
    obj will be an array resembling a JSON object following this syntax:
    {
      Description: {Req: '', Description: ''}
    , Original: ''
    , Translation: {TranslationId: 5, Translation: '', Payload: '', TranslationProvider: ''}
    }
    Adds/Overwrites the Original field with English translation iff possible.
    Will not overwrite for Translation.TranslationId == 1.
  */
  protected function overwriteOriginalWithEnglish($entry){
    $trans       = $entry['Translation'];
    $category    = $trans['TranslationProvider'];
    $fieldSelect = $trans['Payload'];
    //Quit early if possible:
    if($trans['TranslationId'] == 1) return $entry;
    //Trying to fetch existing translation:
    $q = "SELECT Trans FROM Page_DynamicTranslation "
       . "WHERE TranslationId = 1 "
       . "AND Category = '$category' "
       . "AND Field = '$fieldSelect' "
       . "LIMIT 1";
    foreach(DataProvider::fetchAll($q) as $r){//foreach works as if
      $entry['Original'] = $r['Trans'];
    }
    return $entry;
  }
  /**
    @param $tId TranslationId to use
    @param $offset Int the offset for the page to fetch.
    @param $limit Int = 30 the limit for the page to fetch.
    @return $ret [obj] || Exception
    obj will be arrays resembling JSON objects following this syntax:
    {
      Description: {Req: '', Description: ''}
    , Original: ''
    , Translation: {TranslationId: 5, Translation: '', Payload: '', TranslationProvider: ''}
    }
    If $offset === -1, page(…) shall return all elements.
  */
  public function page($tId, $offset, $limit = 30){
    //Sanitizing $tId:
    $tId = is_numeric($tId) ? $tId : 1;
    //Setting up $o as limit+offset sql part
    $offset = is_numeric($offset) ? $offset : -1;
    $limit  = is_numeric($limit)  ? $limit  : 30;
    $o = " LIMIT $limit OFFSET $offset";
    if($offset < 0 || $limit <= 0){$o = '';}
    //Table to use:
    $tableName = $this->getTable();
    //Study to use:
    $study = $this->getStudy(); // String || null
    //Column specific code:
    return $this->withColumn(function($column) use ($tId, $o, $tableName, $study){
      //Fetching $description:
      $description = TranslationTableProjection::fetchDescription($column);
      //Fetching original entries:
      $columnName = $column['columnName'];
      $fieldSelect = $column['fieldSelect'];
      $q = "SELECT $columnName AS columnName, $fieldSelect AS fieldSelect "
         . "FROM $tableName$o";
      $originals = DataProvider::fetchAll($q);
      //Array to return:
      $ret = array();
      //Iterating $originals to fill $ret:
      foreach($originals as $original){
        //$fieldSelect for translation table:
        $fieldSelect = $original['fieldSelect'];
        if($study !== null){
          $fieldSelect = "$study-$fieldSelect";
        }
        //Stub for $entry:
        $entry = array(
          'Description' => $description
        , 'Original' => $original['columnName']
        , 'Translation' => array(
            'TranslationId' => $tId
          , 'Translation' => ''
          , 'Payload' => $fieldSelect
          , 'TranslationProvider' => $column['category']
          )
        );
        //Trying to add existing translation:
        $entry = $this->addTranslation($entry);
        $entry = $this->overwriteOriginalWithEnglish($entry);
        //Pushing $entry:
        array_push($ret, $entry);
      }
      return $ret;
    });
  }
  /**
    @param $tId TranslationId the Translation to search
    @param $searchText String the Text to search
    @param $searchStrategy {'both','translation','original'}
    @return $ret [obj] || Exception
    obj will be arrays resembling JSON objects following this syntax:
    {
      Description: {Req: '', Description: ''}
    , Original: ''
    , Translation: {TranslationId: 5, Translation: '', Payload: '', TranslationProvider: ''}
    }
    Searches for the given $searchText and returns array to allow translation for found entries.
    $searchStrategy specifies if the originals, the translations or both should be searched.
  */
  public function search($tId, $searchText, $searchStrategy = 'both'){
    //Sanitizing $tId:
    $tId = is_numeric($tId) ? $tId : 1;
    //Sanitizing $searchText:
    $searchText = Config::getConnection()->escape_string($searchText);
    //Sanitizing $searchStrategy:
    if(preg_match('/^(both|translation|original)$/', $searchStrategy) === 0){
      return new Exception("Invalid \$searchStrategy: '$searchStrategy'");
    }
    //Table to use:
    $tableName = $this->getTable();
    //Study to use:
    $study = $this->getStudy(); // String || null
    //Column specific code:
    return $this->withColumn(function($column)
      use ($tId, $searchText, $searchStrategy, $tableName, $study){
      $category = $column['category'];
      //Description to use for entries:
      $description = TranslationTableProjection::fetchDescription($column);
      //Payload -> $entry to prevent duplicates
      $payloadMap = array();
      //Searching in originals:
      if($searchStrategy === 'both' || $searchStrategy === 'original'){
        $columnName = $column['columnName'];
        $fieldSelect = $column['fieldSelect'];
        $q = "SELECT $columnName AS columnName, $fieldSelect AS fieldSelect "
           . "FROM $tableName "
           . "WHERE $columnName LIKE '%$searchText%'";
        $originals = DataProvider::fetchAll($q);
        foreach($originals as $original){
          $fieldSelect = $original['fieldSelect'];
          if($study !== null){
            $fieldSelect = "$study-$fieldSelect";
          }
          //Stub for $entry:
          $entry = array(
            'Description' => $description
          , 'Original' => $original['columnName']
          , 'Translation' => array(
              'TranslationId' => $tId
            , 'Translation' => ''
            , 'Payload' => $fieldSelect
            , 'TranslationProvider' => $category
            )
          );
          if($study !== null){
            $entry['Study'] = $study;
          }
          //Trying to add existing translation:
          $entry = $this->addTranslation($entry);
          $entry = $this->overwriteOriginalWithEnglish($entry);
          //Putting $entry into map:
          $payloadMap[$fieldSelect] = $entry;
        }
      }
      //Searching in translations:
      if($searchStrategy === 'both' || $searchStrategy === 'translation'){
        //Setting $columnName and $fieldSelect:
        $columnName = $column['columnName'];
        $fieldSelect = $column['fieldSelect'];
        //Need to fetch all originals to find matching translations:
        $q = "SELECT $columnName AS columnName, $fieldSelect AS fieldSelect "
           . "FROM $tableName ";
        $originals = DataProvider::fetchAll($q);
        foreach($originals as $original){
          $fieldSelect = $original['fieldSelect'];
          if($study !== null){
            $fieldSelect = "$study-$fieldSelect";
          }
          //Preventing possible duplicates:
          if(array_key_exists($fieldSelect, $payloadMap)){ continue; }
          //Checking for translation:
          $q = "SELECT Trans FROM Page_DynamicTranslation "
             . "WHERE TranslationId = $tId "
             . "AND Category = '$category' "
             . "AND Field = '$fieldSelect' "
             . "AND Trans LIKE '%$searchText%' "
             . "LIMIT 1";
          foreach(DataProvider::fetchAll($q) as $r){//foreach works as if
            $entry = array(
              'Description' => $description
            , 'Original' => $original['columnName']
            , 'Translation' => array(
                'TranslationId' => $tId
              , 'Translation' => $r['Trans']
              , 'Payload' => $fieldSelect
              , 'TranslationProvider' => $category
              )
            );
            if($study !== null){
              $entry['Study'] = $study;
            }
            $payloadMap[$fieldSelect] = $entry;
          }
        }
      }
      //Done:
      return array_values($payloadMap);
    });
  }
  /**
    @param $projections [TranslationColumnProjection]
    @param $predicate function($column) -> true || *
    @return $ret [TranslationColumnProjection]
    Filters an array of $projections by applying a given $predicate to their withColumn methods.
    Only the cases where $predicate returns true will be returned in $ret.
  */
  private static function filterColumn($projections, $predicate){
    $ret = array();
    foreach($projections as $prj){
      $chk = $prj->withColumn($predicate);
      if($chk === true){
        array_push($ret, $prj);
      }
    }
    return $ret;
  }
  /**
    @param $projections [TranslationColumnProjection]
    @param $regex PREG pattern, String
    @return $ret [TranslationColumnProjection]
    Filters a given $projections array of TranslationColumnProjection by checking if
    the 'category' entry of their column matches a given $regex.
  */
  public static function filterCategoryRegex($projections, $regex){
    return self::filterColumn($projections, function($column) use ($regex){
      $match = preg_match($regex, $column['category']);
      return $match === 1;
    });
  }
}
