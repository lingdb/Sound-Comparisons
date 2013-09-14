<?php
/***/
abstract class DBTable{
  /**
    @param $q String a query to be exetucted
    @return [$r] the row that was fetched.
    Fetches a single row from a query.
  */
  protected function fetchRow($q){
    $set = $this->dbConnection->query($q);
    if($r = $set->fetch_row())
      return $r;
    return null;
  }
  /***/
  protected function fetchRows($q){
    $rows = array();
    $set  = $this->dbConnection->query($q);
    while($r = $set->fetch_row())
      array_push($rows, $r);
    return $rows;
  }
  /**
    A function String -> String that
    takes a string of fields as between SELECT and FROM
    in a query and transforms it into a query that can be executed.
  */
  protected function buildSelectQuery($fs){
    die('DBTable:buildSelectQuery() needs to be redefined.');
  }
  /***/
  protected function fetchFields(){
    $fs = implode(', ', func_get_args());
    if($q = $this->buildSelectQuery($fs))
      return $this->fetchRow($q);
    return null;
  }
  /***/
  protected function fetchFieldRows(){
    $fs = implode(', ', func_get_args());
    if($q = $this->buildSelectQuery($fs))
      return $this->fetchRows($q);
    return array();
  }
  /***/
  public function exists(){
    $r = $this->fetchFields('COUNT(*)');
    if($r === null) return false;
    return ($r[0] > 0);
  }
}
?>
