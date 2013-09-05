<?php
require_once 'DBTable.php';
/**
  The abstract DBEntry represents something that is saved inside the database.
  A DBEntry has a key and an id.
  The key is what the DBEntry is known as by the user/get parameters,
  the id is a unique characteristic in the database.
  Every DBEntry should have access to the ValueManager and
  thereby to the database.
*/
abstract class DBEntry extends DBTable{
  protected $key;           // Identifier for other cases
  protected $id;            // Identifier to be used with the db
  protected $dbConnection;  // $this->v->getConnection()
  protected $v;             // ValueManager
  /**
    @param $v ValueManager
    Sets up the DBEntry by giving it a ValueManager and a dbConnection.
  */
  protected function setup($v){
    $this->v = $v;
    $this->dbConnection = $v->getConnection();
  }
  /**
    @return $key
    Returns the key associated with a DBEntry.
  */
  public function getKey(){
    return $this->key;
  }
  /**
    @return $id
    Returns the id associated with a DBEntry.
  */
  public function getId(){
    return $this->id;
  }
  /**
    @return $dbConnection mysqlResource
    Returns the dbConnection that comes with a DBEntry.
    This is helpful so that each DBEntry can perform
    actions involving the database.
  */
  public function getConnection(){
    return $this->dbConnection;
  }
  /**
    @return $v ValueManager
    Returns the ValueManager a DBEntry was created with.
    This is useful for two reasons:
    1.: A DBEntry might need to lookup some context so
        that it can act accordingly.
    2.: Because of the immutable nature of the ValueManager
        there might well be more than one instance of it.
  */
  public function getValueManager(){
    return $this->v;
  }
  /**
    @param $x DBEntry
    @param $y DBEntry
  */
  public static function compareOnId($x, $y){
    $a = $x->getId();
    $b = $y->getId();
    if($a === $b) return 0;
    if($a > $b) return 1;
    return -1;
  }
  /**
    @param $set1 DBEntry[]
    @param $set2 DBEntry[]
    @return $union DBEntry[]
  */
  public static function union($set1, $set2){
    $set3 = array();
    foreach($set1 as $s)
      $set3[$s->getId()] = $s;
    foreach($set2 as $s)
      $set3[$s->getId()] = $s;
    return $set3;
  }
  /**
    @param $set1 DBEntry[]
    @param $set2 DBEntry[]
    @return $difference DBEntry[]
  */
  public static function difference($set1, $set2){
    return array_udiff($set1, $set2, 'DBEntry::compareOnId');
  }
  /**
    @param $set1 DBEntry[]
    @param $set2 DBEntry[]
    @return $intersection DBEntry[]
  */
  public static function intersection($set1, $set2){
    return array_uintersect($set1, $set2, 'DBEntry::compareOnId');
  }
  /**
    @param $set1 DBEntry[]
    @param $set2 DBEntry]]
    @return has String matches /^(all|none|some)$/
    'all' will only be returned, if $set2 has the same count
    as the intersection.
  */
  public static function tellIntersection($set1, $set2){
    $c = count(DBEntry::intersection($set1, $set2));
    if($c === count($set2)) return 'all';
    if($c === 0) return 'none';
    return 'some';
  }
  /**
    @param $bunch DBEntry[]
    @return $nub DBEntry[]
    Returns the unique elements from the bunch by their id's.
    Later occurences overwrite earlier ones.
  */
  public static function nub($bunch){
    $nub = array();
    foreach($bunch as $b)
      $nub[$b->getId()] = $b;
    return $nub;
  }
}
?>
