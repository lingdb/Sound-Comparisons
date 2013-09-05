<?php
require_once 'FamilyManager.php';
require_once 'LanguageManager.php';
require_once 'MeaningGroupManager.php';
require_once 'PageViewManager.php';
require_once 'RegionManager.php';
require_once 'SoundfileManager.php';
require_once 'StudyManager.php';
require_once 'TranslationManager.php';
require_once 'UserCleanedManager.php';
require_once 'WordManager.php';
require_once 'WordOrderManager.php';

abstract class ValueManager{
  protected $config;
  protected $dbConnection;
  /** @return $config */
  public function getConfig(){
    return $this->config;
  }
  /** @return $dbConnection */
  public function getConnection(){
    return $this->dbConnection;
  }
  /** String -> SubManager */
  protected $managers = array();
  /**
    @param $manager String - getName() of the requested Manager.
    @return has Bool
  */
  protected function hasM($manager){
    return isset($this->managers[$manager]);
  }
  /**
    @param $manager String - getName() of the requested Manager.
  */
  protected function getM($manager){
    if(array_key_exists($manager, $this->managers))
     return $this->managers[$manager];
    //Initialize $manager:
    switch($manager){
      case 'FamilyManager':
        $this->managers[$manager] = new InitFamilyManager($this);
      break;
      case 'LanguageManager':
        $this->managers[$manager] = new InitLanguageManager($this);
      break;
      case 'MeaningGroupManager':
        $this->managers[$manager] = new InitMeaningGroupManager($this);
      break;
      case 'PageViewManager':
        $this->managers[$manager] = new InitPageViewManager($this);
      break;
      case 'RegionManager':
        $this->managers[$manager] = new InitRegionManager($this);
      break;
      case 'SoundfileManager':
        $this->managers[$manager] = new InitSoundfileManager($this);
      break;
      case 'StudyManager':
        $this->managers[$manager] = new InitStudyManager($this);
      break;
      case 'TranslationManager':
        $this->managers[$manager] = new InitTranslationManager($this);
      break;
      case 'UserCleanedManager':
        $this->managers[$manager] = new InitUserCleanedManager($this);
      break;
      case 'WordManager':
        $this->managers[$manager] = new InitWordManager($this);
      break;
      case 'WordOrderManager':
        $this->managers[$manager] = new InitWordOrderManager($this);
      break;
    }
    return $this->managers[$manager];
  }
  /**
    @param $m SubManager
    @return $ret ValueManager
  */
  public function setM($m){
    //Cloning and updating the ValueManager:
    $ret = clone $this;
    $ret->managers[$m->getName()] = $m;
    //Updating the SubManagers:
    foreach($ret->managers as $k => $sm){
      $ret->managers[$k] = $sm->updateValueManager($ret);
    }
    //Done.
    return $ret;
  }
  /**
   * Produces an array representation of a ValueManager.
   * The inverse to this is done in constructor of the InitValueManager.
   * @return array Array
   * */
  protected function toArray(){
    $ret = array();
    if($this->hasM('FamilyManager')){
      $ret = array_merge($ret, $this->getM('FamilyManager')->pack());
    }
    if($this->hasM('StudyManager')){
      $ret = array_merge($ret, $this->getM('StudyManager')->pack());
    }
    if($this->hasM('RegionManager')){
      $ret = array_merge($ret, $this->getM('RegionManager')->pack());
    }
    if($this->hasM('LanguageManager')){
      $ret = array_merge($ret, $this->getM('LanguageManager')->pack());
    }
    if($this->hasM('MeaningGroupManager')){
      $ret = array_merge($ret, $this->getM('MeaningGroupManager')->pack());
    }
    if($this->hasM('WordManager')){
      $ret = array_merge($ret, $this->getM('WordManager')->pack());
    }
    if($this->hasM('PageViewManager')){
      $ret = array_merge($ret, $this->getM('PageViewManager')->pack());
    }
    if($this->hasM('TranslationManager')){
      $ret = array_merge($ret, $this->getM('TranslationManager')->pack());
    }
    if($this->hasM('WordOrderManager')){
      $ret = array_merge($ret, $this->getM('WordOrderManager')->pack());
    }
    if($this->hasM('UserCleanedManager')){
      $ret = array_merge($ret, $this->getM('UserCleanedManager')->pack());
    }
    return $ret;
  }
  /**
    A debug function to echo all currently safed tuples of (name, value)
  */
  public function show($echo = true){
    $showArray = $this->toArray();
    if(count($showArray)==0)
      return;
    $s = '<ul>';
    foreach($showArray as $n => $v){
      $s =  $s.'<li>'.$n.' => '.$v.'</li>';
    }
    $s = $s.'</ul>';
    if($echo){
      echo $s;
    } else {
      return $s;
    }
  }
  /**
    @param [$target=''] String target url to which parameters will be added
    @param [$attr='href'] String attribute that will contain the link
    @return $link String
    Builds a href attribute for links.
  */
  public function link($target = '', $attr = 'href'){
    $linkArray = $this->toArray();
    $getStr = '';
    foreach($linkArray as $n => $v)
      $getStr = $getStr.'&'.$n.'='.$v;
    if(count($getStr)>0)
      $getStr = '?'.substr($getStr,1);
    return "$attr='".$target.$getStr."'";
  }
  
  /**
    An attribute added to links to determine the kind of pageView
    that the link will load.
    This is meant to lead to a changed link-behaviour with the help of javascript.
    @return $load String
  */
  public function load(){
    return 'load="'.$this->gpv()->getType().'"';
  }
  /**
    Changes the translator to the given TranslationId.
    Makes the ValueManager forget about known spelling-/phonetic-languages.
    Therefore the InitValueManager can infer spelling-/phonetic by RfcLanguage of
    the current translation.
    @param $t TranslationManager
    @return $v ValueManager
  */
  public function setTranslator($t){
    $c = $this->setM($t);
    $c->managers['WordOrderManager'] = null; //This is evil, see Patch from 'Thu Nov 1 14:00:00 CET 2012'
    return $c;
  }
  /*
    Nulls the TranslationManager.
    @return $v ValueManager
  */
  public function cleanTranslator(){
    $c = clone $this;
    $c->managers['TranslationManager'] = null;
    return $c;
  }
}

?>
