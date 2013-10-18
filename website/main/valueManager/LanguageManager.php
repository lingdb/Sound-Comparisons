<?php
require_once 'SubManager.php';

abstract class LanguageManager extends SubManager{
  protected $languages = array();
  /**
   * @return Language[]
   * */
  public function getLanguages(){
    return $this->languages;
  }
  /**
    Sets languages without keepin referential transparency.
    @param $languages Language[]
  */
  public function unsafeSetLanguages($languages){
    $this->languages = $languages;
  }
  /**
   * @param $languages Language[]
   * @return $v ValueManager
   * */
  public function addLanguages($languages){
    $languages = DBEntry::union($this->languages, $languages);
    return $this->setLanguages($languages);
  }
  /**
   * @param $language Language
   * @return $v ValueManager
   * */
  public function addLanguage($language){
    return $this->addLanguages(array($language));
  }
  /**
   * @param $languages Language[]
   * @return $v ValueManager
   * */
  public function delLanguages($languages){
    $languages = DBEntry::difference($this->languages, $languages);
    return $this->setLanguages($languages);
  }
  /**
   * @param $language Language
   * @return $v ValueManager
   * */
  public function delLanguage($language){
    return $this->delLanguages(array($language));
  }
  /**
   * @param language Language
   * @return has Bool
   * */
  public function hasLanguage($language){
    $id = $language->getId();
    foreach($this->languages as $l)
      if($l->getId() == $id)
        return true;
    return false;
  }
  /**
    @param $languages Language[]
    @return has String
    Tells, if 'all', 'none' or 'some' of the languages are hold.
  */
  public function hasLanguages($languages){
    return DBEntry::tellIntersection($this->languages, $languages);
  }
  /**
    Tells if there are as many Languages in the current study
    as there are Languages selected.
    @return has Bool
  */
  public function hasAllLanguages(){
    $studyLangCount   = count($this->gvm()->gsm()->getStudy()->getLanguages());
    $selectLangCount  = count($this->getLanguages());
    return ($studyLangCount === $selectLangCount);
  }
  /**
   * @param $language Language
   * @return $v ValueManager
   * */
  public function setLanguage($language){
    return $this->setLanguages(array($language));
  }
  /**
   * @param $languages Language[]
   * @return $v ValueManager
   * */
  public function setLanguages($languages = null){
    if(!isset($languages))
      $languages = array();
    $l = clone $this;
    $l->languages = $languages;
    return $this->gvm()->setM($l);
  }
  /** Overwrites SubManager:pack() */
  public function pack(){
    if(count($this->languages) <= 0)
      return array();
    $lNames = array();
    foreach($this->languages as $l)
      array_push($lNames, $this->encodeUrl($l->getKey()));
    return array('languages' => implode(',',$lNames));
  }
  /***/
  public function getDefaults($multiple = false){
    $s = $this->gvm()->getStudy();
    if($multiple)
      return $s->getDefaultLanguages();
    return array($s->getDefaultLanguage());
  }
}

class InitLanguageManager extends LanguageManager{
  /**
    @param $v ValueManager
  */
  public function __construct($v){
    $this->setValueManager($v);
    if(isset($_GET['languages'])){
      $arr = array_unique(explode(',',$_GET['languages']));
      foreach($arr as $v){
        $l = new LanguageFromKey($this->gvm(), $this->decodeUrl($v));
        array_push($this->languages, $l);
      }
    }
  }
  /***/
  public function getName(){
    return "LanguageManager";
  }
}

?>
