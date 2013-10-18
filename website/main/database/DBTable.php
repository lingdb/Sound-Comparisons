<?php
/***/
abstract class DBTable{
  /***/
  protected function fetchOneBy($q, $f = null){
    if($f === null)
      $f = function($set){return $set->fetch_row();};
    $set = Config::getConnection()->query($q);
    return $f($set);
  }
  /***/
  protected function fetchAllBy($q, $f = null){
    if($f === null)
      $f  = function($set){return $set->fetch_row();};
    $rows = array();
    $set  = Config::getConnection()->query($q);
    for($i = 0; $i < $set->num_rows; $i++)
      array_push($rows, $f($set));
    return $rows;
  }
  /**
    A function String -> String that
    takes a string of fields as between SELECT and FROM
    in a query and transforms it into a query that can be executed.
  */
  protected function buildSelectQuery($fs){
    Config::error('DBTable:buildSelectQuery() needs to be redefined.');
  }
  /***/
  protected function fetchHelper($fields, $all = false, $assoc = false){
    if($q = $this->buildSelectQuery($fields)){
      $f = $assoc ? function($set){return $set->fetch_assoc();}
                  : function($set){return $set->fetch_row();  };
      if($all) return $this->fetchAllBy($q, $f);
      return $this->fetchOneBy($q, $f);
    }
    return null;
  }
  /***/
  protected function fetchFields(){
    $fs = implode(', ', func_get_args());
    return $this->fetchHelper($fs);
  }
  /***/
  protected function fetchFieldRows(){
    $fs = implode(', ', func_get_args());
    return $this->fetchHelper($fs, true);
  }
  /***/
  protected function fetchAssoc(){
    $fs = implode(', ', func_get_args());
    return $this->fetchHelper($fs, false, true);
  }
  /***/
  protected function fetchAssocs(){
    $fs = implode(', ', func_get_args());
    return $this->fetchHelper($fs, true, true);
  }
  /***/
  public function exists(){
    $r = $this->fetchFields('COUNT(*)');
    if($r === null) return false;
    return ($r[0] > 0);
  }
}
?>
