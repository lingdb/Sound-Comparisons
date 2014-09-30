<?php
  abstract class PageView{
    protected $valueManager;

    public function __construct($v){
      $this->valueManager = $v;
    }

    public abstract function getType();

    public abstract function displayName();
    
    public abstract function init();

    public function hasName($name){
      return $name === get_class($this);
    }
  }
?>
