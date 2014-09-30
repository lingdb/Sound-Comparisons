<?php
require_once 'SubManager.php';

abstract class RegionManager extends SubManager{
  protected $regions = array();
  /** @return $regions Region[] */
  public function getRegions(){
    return $this->regions;
  }
  /**
   * @param $regions Region[]
   * @return $v ValueManager
   * */
  public function setRegions($regions = array()){
    $r = clone $this;
    $r->regions = $regions;
    return $this->gvm()->setM($r);
  }
  /**
   * @param $region Region
   * @return $v ValueManager
   * */
  public function setRegion($region){
    return $this->setRegions(array($region));
  }
  /**
   * @param $region Region
   * @return $v ValueManager
   * */
  public function addRegion($region){
    return $this->addRegions(array($region));
  }
  /**
    @param $regions Region[]
    @return $v ValueManager
  */
  public function addRegions($regions){
    $regions = DBEntry::union($this->regions, $regions);
    return $this->setRegions($regions);
  }
  /**
   * @param $region Region
   * @return $has Bool
   * */
  public function hasRegion($region){
    $id = $region->getId();
    foreach($this->regions as $r)
      if($r->getId() == $id)
        return true;
    return false;
  }
  /**
    @param $regions Region[]
    @return has String
    Tells, if 'all', 'none' or 'some' of the regions are hold.
  */
  public function hasRegions($regions){
    return DBEntry::tellIntersection($this->regions, $regions);
  }
  /**
   * @param $region Region
   * @return $v ValueManager
   * */
  public function delRegion($region){
    return $this->delRegions(array($region));
  }
  /**
    @param $regions Region[]
    @return $v ValueManager
  */
  public function delRegions($regions){
    $regions = DBEntry::difference($this->regions, $regions);
    return $this->setRegions($regions);
  }
  /** Overwrites SubManager:pack() */
  public function pack(){
    if(count($this->regions) <= 0)
      return array();
    $rNames = array();
    foreach($this->regions as $r)
      array_push($rNames, $this->encodeUrl($r->getKey()));
    return array('regions' => implode(',',$rNames));
  }
}

class InitRegionManager extends RegionManager{
  /**
    @param $v ValueManager
  */
  public function __construct($v){
    $this->setValueManager($v);
    if(isset($_GET['regions'])){
      $arr = array_unique(explode(',',$_GET['regions']));
      foreach($arr as $v){
        $r = new RegionFromKey($this->gvm(), $this->decodeUrl($v));
        array_push($this->regions, $r);
      }
    }
  }
  /***/
  public function getName(){return "RegionManager";}
}

?>
