<?php
  /**
    The DynamicTranslationProvider generalises the way
    Dynamic Translation works, to add in more magic.
  */
  require_once "TranslationProvider.php";
  abstract class DynamicTranslationProvider extends TranslationProvider{
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
    //FIXME remove this entirely, because getName already incorporates table and column, so that the update method from TranslationProvider still works.
//  public abstract function updateColumn($c, $tId, $payload, $update);
//  public function update($tId, $payload, $update){
//    return $this->updateColumn($this->getColumn(), $tId, $payload, $update);
//  }
    /*An offset function depending on a Column.*/
    //FIXME check if dependence on a column is necessary.
    public abstract function offsetsColumn($c, $tId, $study);
    public function offsets($tId, $study){
      return $this->offsetsColumn($this->getColumn(), $tId, $study);
    }
    /*A page function depending on a Column.*/
    public abstract function pageColumn($c, $tId, $study, $offset);
    public function page($tId, $study, $offset){
      return $this->pageColumn($this->getColumn(), $tId, $study, $offset);
    }
    /**
      @param c Column to translate
      @return array('description' => ?, 'origCol' => ?)
      Used to translate vital data in DynamicTranslationProviders.
      This is useful in the page and search functions,
      and once was a part of search only, before page was introduced.
    */
    public abstract function translateColumn($c);
  }
?>
