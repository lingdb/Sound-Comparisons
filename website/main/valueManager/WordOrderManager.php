<?php
require_once 'SubManager.php';

abstract class WordOrderManager extends SubManager{
  protected $isLogical = false; // Bool
  protected $spLang    = null;  // Language to sort in alphabetically
  protected $phLang    = null;  // Language to get the phonetics from
  /** @return $isLogical Bool */
  public function isLogical(){
    return $this->isLogical;
  }
  /** @return $v ValueManager */
  public function setLogical(){
    $w = clone $this;
    $w->isLogical = true;
    return $this->gvm()->setM($w);
  }
  /** @return isAlphabetical Bool */
  public function isAlphabetical(){
    return !$this->isLogical;
  }
  /** @return $v ValueManager */
  public function setAlphabetical(){
    $w = clone $this;
    $w->isLogical = false;
    return $this->gvm()->setM($w);
  }
  /** @return $spLang Language */
  public function getSpLang(){
    return $this->spLang;
  }
  /**
    @param [$spl] Language
    @return $v ValueManager
  */
  public function setSpLang($spl = null){
    $c = clone $this;
    $c->spLang = $spl;
    $c->phLang = ($spl !== null) ? $spl->getPhoneticLanguage() : null;
    return $this->gvm()->setM($c);
  }
  /** @return $phLang Language */
  public function getPhLang(){
    return $this->phLang;
  }
  /**
    @param $phl Language
    @return $v ValueManager
  */
  public function setPhLang($phl){
    $c = clone $this;
    $c->phLang = $phl;
    return $this->gvm()->setM($c);
  }
  /** @return $v ValueManager */
  public function clear(){
    $c = clone $this;
    $c->isLogical = false;
    $c->spLang    = null;
    $c->phLang    = null;
    return $this->gvm()->setM($c);
  }
  /** Overwrites SubManager:pack() */
  public function pack(){
    $ret = array();
    if($this->isLogical){
      $ret['wo_order'] = 'logical';
    }else{
      $ret['wo_order'] = 'alphabetical';
    }
    if($this->spLang)
      $ret['wo_spLang'] = $this->spLang->getKey();
    if($this->phLang)
      $ret['wo_phLang'] = $this->phLang->getKey();
    return $ret;
  }
}

class InitWordOrderManager extends WordOrderManager{
  /**
    @param $v ValueManager
  */
  public function __construct($v){
    $this->setValueManager($v);
    $db = $this->getConnection();
    //Get parameters:
    if(isset($_GET['wo_order'])){
      $l = $db->escape_string($_GET['wo_order']);
      if($l == 'logical'){
        $this->isLogical = true;
      }else if($l == 'alphabetical'){
        $this->isLogical = false;
      }
    }
    if(isset($_GET['wo_spLang'])){
      $key = $db->escape_string($_GET['wo_spLang']);
      $this->spLang = new LanguageFromKey($v, $key);
    }
    if(isset($_GET['wo_phLang'])){
      $key = $db->escape_string($_GET['wo_phLang']);
      $this->phLang = new LanguageFromKey($v, $key);
    }
    //Spelling/Phonetic Languages:
    if(!$this->isLogical||!$this->spLang||!$this->phLang){
      $rLang = $v->getTranslator()->getRfcLanguage();
      if($rLang){
        if(!$this->spLang)
          $this->spLang = $rLang;
        if(!$this->phLang)
          $this->phLang = $rLang;
      }
      //Setting phLang per default:
      if(!$this->phLang){
        $sId = $v->getStudy()->getId();
        $q   = "SELECT LanguageIx FROM Languages_$sId "
             . "WHERE IsOrthographyHasNoTranscriptions = 0 "
             . "OR IsOrthographyHasNoTranscriptions IS NULL "
             . "LIMIT 1";
        $r   = $v->getConnection()->query($q)->fetch_row();
        $this->phLang = new LanguageFromId($v, $r[0]);
      }
    }
  }
  /***/
  public function getName(){
    return 'WordOrderManager';
  }
}

?>
