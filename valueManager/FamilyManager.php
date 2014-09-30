<?php
require_once 'SubManager.php';

abstract class FamilyManager extends SubManager{
  protected $families = array();
  /***/
  public function getFamilies(){
    return $this->families;
  }
  /***/
  public function setFamilies($families = array()){
    $f = clone $this;
    $f->families = $families;
    return $this->gvm()->setM($f);
  }
  /***/
  public function addFamilies($fs){
    $families = DBEntry::union($this->families, $fs);
    return $this->setFamilies($families);
  }
  /***/
  public function addFamily($f){
    return $this->addFamilies(array($f));
  }
  /***/
  public function delFamilies($fs){
    $families = DBEntry::difference($this->families, $fs);
    return $this->setFamilies($families);
  }
  /***/
  public function delFamily($f){
    return $this->delFamilies(array($f));
  }
  /***/
  public function hasFamily($f){
    foreach($this->families as $family)
      if($family->getId() === $f->getId())
        return true;
    return false;
  }
  /** Overwrites SubManager:pack() */
  public function pack(){
    if(count($this->families) <= 0)
      return array();
    $fNames = array();
    foreach($this->families as $f)
      array_push($fNames, $this->encodeUrl($f->getKey()));
    return array('families' => implode(',',$fNames));
  }
}

class InitFamilyManager extends FamilyManager{
  /***/
  public function __construct($v){
    $this->setValueManager($v);
    if(isset($_GET['families'])){
      foreach(array_unique(explode(',',$_GET['families'])) as $key){
        $key = $this->decodeUrl($key);
        $f   = new FamilyFromKey($this->gvm(), $key);
        $this->families[$f->getId()] = $f;
      }
    }
  }
  /***/
  public function getName(){return "FamilyManager";}
}
?>
