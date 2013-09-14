<?php
require_once 'SubManager.php';
require_once 'pageViews/LanguageView.php';
require_once 'pageViews/MapView.php';
require_once 'pageViews/MultiTransposed.php';
require_once 'pageViews/MultiView.php';
require_once 'pageViews/WhoAreWeView.php';
require_once 'pageViews/WordView.php';

abstract class PageViewManager extends SubManager{
  protected $pageViews = array();
  protected $viewType  = '';
  /**
    @return name String
  */
  public function displayName(){
    return $this->pageViews[$this->viewType]->displayName();
  }
  /**
    @return viewType String
  */
  public function getType(){
    return $this->pageViews[$this->viewType]->getType();
  }
  /**
    @param $viewType String
    @return $v ValueManager
  */
  public function setView($viewType){
    $pvm = clone $this;
    $pvm->viewType = $viewType;
    return $this->gvm()->setM($pvm);
  }
  public function isView($name){
    return $this->pageViews[$this->viewType]->hasName($name);
  }
  /**
    True <-> isMultiView || isMultiTransposed
    @return is Bool 
  */
  public function isSelection(){
    return ($this->isView('MultiView') || $this->isView('MultiTransposed'));
  }
  /**
    Flips between the two multiviews if PageView is one of them.
    If !isSelection or !isMapView this method dies.
    @return $v ValueManager
  */
  public function transpose(){
    if(!$this->isSelection() || $this->isView('MapView'))
      return die('Could not transpose PageView:\t'.$this->getType());
    if($this->isView('MultiView'))
      return $this->setView('MultiTransposed');
    if($this->isView('MultiTransposed'))
      return $this->setView('MultiView');
  }
  /** Overwrites SubManager:pack() */
  public function pack(){ return array('pageView' => $this->getType()); }
}

class InitPageViewManager extends PageViewManager{
  /**
    @param $v ValueManager
  */
  public function __construct($v){
    $this->setValueManager($v);
    //Filling the pageViews:
    foreach(array(
      new LanguageView($v)
    , new MapView($v)
    , new MultiTransposed($v)
    , new MultiView($v)
    , new WhoAreWeView($v)
    , new WordView($v)
    ) as $pv){
      $this->pageViews[$pv->getType()] = $pv;
      $this->pageViews[get_class($pv)] = $pv;
    }
    //Making sure WordManager and LanguageManager are initialized:
    $v->gwm(); $v->glm();
    if(isset($_GET['pageView'])){
      $this->init($this->getConnection()->escape_string($_GET['pageView']));
    }else{
      $this->init('mapView');
    }
  }
  /**
    @param viewType String
  */
  protected function init($viewType){
    $this->viewType = $viewType;
    $this->pageViews[$viewType]->init();
  }
  /***/
  public function getName(){return 'PageViewManager';}
}
?>
