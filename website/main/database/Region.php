<?php
require_once 'DBEntry.php';
/**
  These constants provide names for the expected values of
  the RegionGpTypeIx field in the v4.Regions table:
*/
define("REGIONTYPE_NORMAL",   1);
define("REGIONTYPE_HEADLINE", 0);
define("REGIONTYPE_SPELLING", 2);
/**
  The Region is an entry of the Region table in the database.
*/
class Region extends DBEntry {
  /***/
  public function buildSelectQuery($fs){
    $sid = $this->getValueManager()->getStudy()->getId();
    $id  = $this->id;
    return "SELECT $fs FROM Regions_$sid WHERE CONCAT(StudyIx, FamilyIx, SubFamilyIx, RegionGpIx) = $id";
  }
  /**
    @return $shortName String
    Fetches the ShortName of a Region.
    If the ValueManager, that the Region was created with,
    holds a Translator, the ShortName is tried to be translated
    into the current site language.
  */
  public function getShortName(){
    if($trans = $this->getValueManager()->getTranslator()->dt($this)){
      if($trans[0] != '')
        return $trans[0];
    }
    return current($this->fetchFields('RegionGpNameShort'));
  }
  /**
    @return $name String
    Returns the Name of the Region.
  */
  public function getName(){
    if($trans = $this->getValueManager()->getTranslator()->dt($this)){
      if($trans[1] != '')
        return $trans[1];
    }
    return current($this->fetchFields('RegionGpNameLong'));
  }
  /**
    @return family Family
  */
  public function getFamily(){
    $r = $this->fetchFields('CONCAT(StudyIx, FamilyIx)');
    return new FamilyFromId($this->getValueManager(), $r[0]);
  }
  /***/
  public function getColor(){
    if($this->getValueManager()->getStudy()->getColorByFamily())
      return $this->getFamily()->getColor();
    return current($this->fetchFields('Color'));
  }
  /***/
  public function getColorStyle(){
    $color = $this->getColor();
    if($color !== '')
      return " style='background-color: #$color;'";
    return '';
  }
  /**
    @param [$allowNoTranscriptions = false]
    @returns $languages Language[]
    Returns all Languages contained in a Region.
  */
  public function getLanguages($allowNoTranscriptions = false){
    $sid = $this->getValueManager()->getStudy()->getId();
    $id  = $this->id;
    $q = "SELECT LanguageIx FROM RegionLanguages_$sid "
       . "WHERE CONCAT(StudyIx, FamilyIx, SubFamilyIx, RegionGpIx) = $id "
       . "ORDER BY RegionMemberLgIx";
    $set = Config::getConnection()->query($q);
    $ret = array();
    while($r = $set->fetch_row()){
      $l = new LanguageFromId($this->v, $r[0]);
      if($l->hasTranscriptions() || $allowNoTranscriptions)
        array_push($ret, $l);
    }
    return $ret;
  }
  /**
    @return $count The number of languages in this region.
  */
  public function getLanguageCount(){
    $sid = $this->getValueManager()->getStudy()->getId();
    $id  = $this->id;
    $q = "SELECT COUNT(LanguageIx) FROM RegionLanguages_$sid "
       . "WHERE CONCAT(StudyIx, FamilyIx, SubFamilyIx, RegionGpIx) = $id";
    if($r = Config::getConnection()->query($q)->fetch_row())
      return $r[0];
    return 0;
  }
  /**
    @returns $historical Bool
    A Region is historical, if either it's Name or it's ShortName
    contain the substring 'Historical'.
  */
  public function isHistorical(){
    $regex = '/Historical/';
    $name  = $this->getName();
    if(preg_match($regex, $name))
      return true;
    $shortName = $this->getShortName();
    if(preg_match($regex, $shortName))
      return true;
    return false;
  }
  /*
    @return $type Int
    Regions have different types attached to aid their display style in a multitable.
    The types are defined at the top of this file.
  **/
  public function getType(){
    if($r = $this->fetchFields('RegionGpTypeIx'))
      return $r[0];
    return REGIONTYPE_NORMAL;
  }
}
/**
  Adds a constructor to Region that allowes to create Regions from a ValueManager and a key.
*/
class RegionFromKey extends Region{
  /**
    @param $v ValueManager
    @param $key String the Regionname
  */
  public function __construct($v, $key){
    $this->setup($v);
    $this->key = $key;
    $q = "SELECT CONCAT(StudyIx, FamilyIx, SubFamilyIx, RegionGpIx) "
       . "FROM Regions "
       . "WHERE RegionGpNameLong LIKE '$key' "
       . "OR RegionGpNameShort LIKE '$key'";
    if($r = Config::getConnection()->query($q)->fetch_row())
      $this->id = $r[0];
    else Config::error('Invalid RegionKey: ' . $key);
  }
}
/**
  Adds a constructor to Region that allowes to create Regions from a ValueManager and an id.
*/
class RegionFromId extends Region{
  /**
    @param $v ValueManager
    @param $id Int the RegionId
  */
  public function __construct($v, $id){
    $this->setup($v);
    $this->id = $id;
    if($r = $this->fetchFields('RegionGpNameLong')){
      $this->key = $r[0];
    }else Config::error('Invalid RegionId: ' . $id);
  }
}
?>
