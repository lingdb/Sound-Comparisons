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
    return $this->withColumn(function($column){
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
    //Setting up $o as limit+offset sql part
    $offset = is_numeric($offset) ? $offset : -1;
    $limit  = is_numeric($limit)  ? $limit  : 30;
    $o = " LIMIT $limit OFFSET $offset";
    if($offset < 0 || $limit <= 0){$o = '';}
    //Table to use:
    $tableName = $this->getTable();
    //Column specific code:
    return $this->withColumn(function($column) use ($tId, $tableName){
      //FIXME COMPARE LanguagesTranslationProvider.pageColumn(…)
      //FIXME IMPLEMENT
    });
  }
}
