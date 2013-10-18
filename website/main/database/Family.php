<?php
require_once 'DBEntry.php';
/***/
class Family extends DBEntry{
  /***/
  public function getName(){
    if($name = $this->v->getTranslator()->dt($this))
      return $name;
    return $this->key;
  }
  /***/
  public function getAbbr(){
    $id   = $this->id;
    $q    = "SELECT FamilyAbbrAllFileNames FROM Families WHERE CONCAT(StudyIx, FamilyIx) = $id";
    $set  = Config::getConnection()->query($q);
    if($r = $set->fetch_row())
      return $r[0];
    return '';
  }
  /***/
  public function getColor(){
    $id   = $this->id;
    $q    = "SELECT FamilyColorOnWebsite FROM Families WHERE CONCAT(StudyIx, FamilyIx) = $id";
    $set  = Config::getConnection()->query($q);
    if($r = $set->fetch_row())
      return $r[0];
    Config::error('No color for Family:\t'.$this->getName());
  }
  /***/
  public function getRegions(){
    $id   = $this->id;
    $sKey = $this->v->getStudy()->getKey();
    $q    = "SELECT CONCAT(StudyIx, FamilyIx, SubFamilyIx, RegionGpIx) FROM Regions_$sKey "
          . "WHERE CONCAT(StudyIx, FamilyIx) = $id";
    $set  = Config::getConnection()->query($q);
    $regs = array();
    while($r = $set->fetch_row()){
      array_push($regs, new RegionFromId($this->v, $r[0]));
    }
    return $regs;
  }
  /**
    @param [$allowNoTranscriptions = false]
    @return $languages language[]
    Returns all languages in a Family
  */
  public function getLanguages($allowNoTranscriptions = false){
    $langs = array();
    foreach($this->getRegions() as $r)
      $langs = DBEntry::union($langs, $r->getLanguages($allowNoTranscriptions));
    return $langs;
  }
}
/***/
class FamilyFromId extends Family{
  /***/
  public function __construct($v, $id){
    $this->setup($v);
    $this->id  = $id;
    $q    = "SELECT FamilyNm FROM Families WHERE CONCAT(StudyIx, FamilyIx) = $id";
    $set  = Config::getConnection()->query($q);
    if($r = $set->fetch_row()){
      $this->key = $r[0];
    }else Config::error("Invalid FamilyId: $id");
  }
}
/***/
class FamilyFromKey extends Family{
  /***/
  public function __construct($v, $key){
    $this->setup($v);
    $this->key = $key;
    $q = "SELECT CONCAT(StudyIx, FamilyIx) FROM Families WHERE FamilyNm = '$key'";
    if($r = Config::getConnection()->query($q)->fetch_row()){
      $this->id = $r[0];
    }else Config::error("Invalid FamilyNm: $key");
  }
}
?>
