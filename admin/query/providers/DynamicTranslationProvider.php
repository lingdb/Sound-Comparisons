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
    public abstract function searchColumn($c, $tId, $searchText, $searchAll = false);
    public function search($tId, $searchText, $searchAll = false){
      return $this->searchColumn($this->getColumn(), $tId, $searchText, $searchAll);
    }
    /*An offset function depending on a Column.*/
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
    /***/
    public static function getChanged($tId){
      $ret = array();
      if($tId !== 1){
        $q = "SELECT Category, Field, Trans FROM Page_DynamicTranslation WHERE TranslationId = 1";
        foreach(DataProvider::fetchAll($q) as $r){
          $c = $r['Category'];
          $f = $r['Field'];
          $q = "SELECT Trans FROM Page_DynamicTranslation "
             . "WHERE Category = '$c' AND Field = '$f' AND TranslationId = $tId "
             . "AND Time < (SELECT Time FROM Page_DynamicTranslation "
             . "WHERE Category = '$c' AND Field = '$f' AND TranslationId = 1)";
          foreach(DataProvider::fetchAll($q) as $x){
            $desc = Translation::categoryToDescription($c);
            array_push($ret, array(
              'Description' => $desc
            , 'Original'    => $r['Trans']
            , 'Translation' => array(
                'TranslationId'       => $tId
              , 'Translation'         => $x['Trans']
              , 'Payload'             => $f
              , 'TranslationProvider' => $c
              )
            ));
          }
        }
      }
      return $ret;
    }
  }
?>
