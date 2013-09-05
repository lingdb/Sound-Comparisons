<?php
  /**
    The DynamicSearchProvider generalises the way
    Dynamic Translation works, to add in more magic.
  */
  require_once "SearchProvider.php";
  abstract class DynamicSearchProvider extends SearchProvider{
    /* Supplies the name of the Table a Provider takes care of. */
    public abstract function getTable();
    /* Supplies the name of the Column a Provider takes care of.*/
    protected $column;
    public function __construct($column, $dbConnection){
      parent::__construct($dbConnection);
      $this->column = $column;
    }
    public function getColumn(){
      return $this->column;
    }
    public function getName(){
      $n = parent::getName();
      $t = $this->getTable();
      $c = $this->getColumn();
      return "$n-$t-$c";
    }
    /*A search function depending on the Column allowes to reuse a Query for different Columns.*/
    public abstract function searchColumn($c, $tId, $searchText);
    public function search($tId, $searchText){
      return $this->searchColumn($this->getColumn(), $tId, $searchText);
    }
    /*An update function depending on the Column allowes to supply updates focussing on single cells.*/
    public abstract function updateColumn($c, $tId, $payload, $update);
    public function update($tId, $payload, $update){
      return $this->updateColumn($this->getColumn(), $tId, $payload, $update);
    }
  }
?>
