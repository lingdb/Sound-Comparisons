<?php
require_once 'SubManager.php';

abstract class MeaningGroupManager extends SubManager{
  protected $meaningGroups = array();
  protected $setEmpty = false;
  /**
    @param $mg MeaningGroup
    @return has Bool
  */
  public function hasMeaningGroup($mg){
    return array_key_exists($mg->getId(), $this->meaningGroups);
  }
  /**
    @param $mg MeaningGroup
    @return $v ValueManager
  */
  public function addMeaningGroup($mg){
    return $this->setMeaningGroups(array_merge(
        $this->meaningGroups
      , array($mg->getId() => $mg)));
  }
  /**
    @param mg MeaningGroup
    @return $v ValueManager
  */
  public function delMeaningGroup($mg){
    $mgs = array_diff_key($this->meaningGroups, array($mg->getId() => $mg));
    if(count($mgs) === 0){
      return $this->setMeaningGroups();
    }
    return $this->setMeaningGroups($mgs);
  }
  /**
    @param $mg MeaningGroup
    @return $v ValueManager
  */
  public function toggleMeaningGroup($mg){
    if($this->hasMeaningGroup($mg))
      return $this->delMeaningGroup($mg);
    return $this->addMeaningGroup($mg);
  }
  /**
    @return $v ValueManager
  */
  public function cleanMeaningGroups(){
    return $thos->setMeaningGroups(array());
  }
  /**
    @param $mgs MeaningGroup[]
    @return $v ValueManager
  */
  public function setMeaningGroups($mgs = null){
    $mgm = clone $this;
    if(is_null($mgs)){
      $mgm->setEmpty = true;
      $mgm->meaningGroups = array();
    }else if(is_array($mgs)){
      $mgm->meaningGroups = $mgs;
    }else return $this->gvm();
    return $this->gvm()->setM($mgm);
  }
  /** Overwrites SubManager:pack() */
  public function pack(){
    if(count($this->meaningGroups) <= 0){
      if($this->setEmpty){
        return array('meaningGroups' => null);
      }
      return array();
    }
    $cmgs = array();
    foreach($this->meaningGroups as $mg){
      array_push($cmgs, $mg->getKey());
    }
    return array('meaningGroups' => implode(',', $cmgs));
  }
}

class InitMeaningGroupManager extends MeaningGroupManager{
  /**
    @param $v ValueManager
  */
  public function __construct($v){
    $this->setValueManager($v);
    if(isset($_GET['meaningGroups'])){
      if($_GET['meaningGroups'] === ''){
        //The meaningGroups stay empty in this case.
        $this->setEmpty = true;
      }else{
        $arr = array_unique(explode(',', $_GET['meaningGroups']));
        foreach($arr as $m){
          $mg = new MeaningGroupFromKey($this->gvm(), $this->decodeUrl($m));
          $this->meaningGroups[$mg->getId()] = $mg;
        }
      }
    }else{
      $mgs = $this->gvm()->getStudy()->getMeaningGroups();
      $mg = array_shift($mgs);
      $this->meaningGroups[$mg->getId()] = $mg;
    }
  }
  /***/
  public function getName(){return "MeaningGroupManager";}
}

?>
