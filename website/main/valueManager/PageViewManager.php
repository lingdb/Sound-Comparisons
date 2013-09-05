<?php
require_once 'SubManager.php';

abstract class PageViewManager extends SubManager{
  protected $viewType   = 'mapView';
  /**
    @return name String
  */
  public function displayName(){
    $t = $this->gvm()->getTranslator();
    if($this->isSingleView()){
      return $t->st('topmenu_views_wordview');
    }else if($this->isLanguageView()){
      return $t->st('topmenu_views_languageview');
    }else if($this->isMultiView()){
      return $t->st('topmenu_views_multiview');
    }else if($this->isMultiTransposed()){
      return $t->st('topmenu_views_multitransposed');
    }else if($this->isMapView()){
      return $t->st('topmenu_views_mapview');
    }
    die('Call of displayName() on invalid ViewType.');
  }
  /**
    @return viewType String
  */
  public function getType(){
    return $this->viewType;
  }
  /**
    @param $viewType String
    @return $v ValueManager
  */
  protected function setView($viewType){
    $pvm = clone $this;
    $pvm->viewType = $viewType;
    return $this->gvm()->setM($pvm);
  }
  /**
    @return is Bool
  */
  public function isSingleView(){
    return ($this->viewType == 'singleWordView');
  }
  /**
    @return $v ValueManager
  */
  public function setSingleView(){
    return $this->setView('singleWordView');
  }
  /**
    @return is Bool
  */
  public function isLanguageView(){
    return ($this->viewType == 'languageView');
  }
  /**
    @return copy CopyValueManager
  */
  public function setLanguageView(){
    return $this->setView('languageView');
  }
  /**
    @return is Bool
  */
  public function isMultiView(){
    return ($this->viewType == 'multiWordView');
  }
  /**
    @return copy CopyValueManager
  */
  public function setMultiView(){
    return $this->setView('multiWordView');
  }
  /**
    @return is Bool
  */
  public function isMultiTransposed(){
    return ($this->viewType == 'multiViewTransposed');
  }
  /**
    @return copy CopyValueManager
  */
  public function setMultiTransposed(){
    return $this->setView('multiViewTransposed');
  }
  /**
    True <-> isMultiView || isMultiTransposed
    @return is Bool 
  */
  public function isSelection(){
    return ($this->isMultiView() || $this->isMultiTransposed());
  }
  /**
    @return is Bool
  */
  public function isMapView(){
    return ($this->viewType == 'mapView');
  }
  /**
    @return $v ValueManager
  */
  public function setMapView(){
    $c = clone $this;
    $c->viewType = 'mapView';
    return $this->gvm()->setM($c);
  }
  /**
    Flips between the two multiviews if PageView is one of them.
    If !isSelection or !isMapView this method dies.
    @return $v ValueManager
  */
  public function transpose(){
    if(!$this->isSelection() || $this->isMapView())
      return die('Could not transpose PageView:\t'.$this->getType());
    if($this->isMultiView())
      return $this->setMultiTransposed();
    if($this->isMultiTransposed())
      return $this->setMultiView();
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
    //Making sure WordManager and LanguageManager are initialized:
    $v->gwm(); $v->glm();
    if(isset($_GET['pageView'])){
      $this->init(mysql_real_escape_string($_GET['pageView']));
    }else{
      $this->init('mapView');
    }
  }
    /**
    @param v ValueManager
    @param viewType String
  */
  protected function init($viewType){
    $this->viewType = $viewType;
    $v = $this->gvm();
    /*viewType dependent*/
    if($this->isSingleView()){
      if($v->isUserCleaned()) return;
      if(count($v->getWords()) == 0){
        $words = $v->gwm()->getDefaults();
        $v->unsafeSetWords($words);
        $v->unsafeSetLanguages(array());
      }
    }else if($this->isLanguageView()){
      if($v->isUserCleaned()) return;
      if(count($v->getLanguages()) == 0){
        $languages = $v->glm()->getDefaults();
        $v->unsafeSetLanguages($languages);
        $v->unsafeSetWords(array());
      }
    }else if($this->isSelection()){
      if($v->isUserCleaned()) return;
      if((count($v->getWords()) == 0) && (count($v->getLanguages()) == 0)){
        //Setting words:
        $words = $v->gwm()->getDefaults(true);
        $v->unsafeSetWords($words);
        //Setting Languages:
        $languages = $v->glm()->getDefaults(true);
        $v->unsafeSetLanguages($languages);
      }
    }else if($this->isMapView()){
      $studyId = $v->getStudy()->getId();
      if(!$v->hasWord()){ //Find a default word
        $words = $v->gwm()->getDefaults();
        $v->unsafeSetWords($words);
      }
      if($v->isUserCleaned()) return;
      if(count($v->getLanguages()) == 0){ //Find default languages
        //Paul noted the mapView shall default to all languages:
        $languages = $v->getStudy()->getLanguages();
        $v->unsafeSetLanguages($languages);
      }
    }
  }
  /***/
  public function getName(){return 'PageViewManager';}
}

?>
