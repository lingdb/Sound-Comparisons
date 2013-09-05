<?php
require_once 'ValueManager.php';

class RedirectingValueManager extends ValueManager{
  /**
    @param $dbConnection
  */
  public function __construct($dbConnection, $config){
    $this->dbConnection = $dbConnection;
    $this->config       = $config;
  }
  /*-- Shortcut functions below --*/
  public function gfm(){
    return $this->getM('FamilyManager');
  }
  public function glm(){
    return $this->getM('LanguageManager');
  }
  public function gmgm(){
    return $this->getM('MeaningGroupManager');
  }
  public function gpv(){
    return $this->getM('PageViewManager');
  }
  public function grm(){
    return $this->getM('RegionManager');
  }
  public function gsm(){
    return $this->getM('StudyManager');
  }
  public function gsfm(){
    return $this->getM('SoundfileManager');
  }
  public function gtm(){
    return $this->getM('TranslationManager');
  }
  public function gucm(){
    return $this->getM('UserCleanedManager');
  }
  public function gwm(){
    return $this->getM('WordManager');
  }
  public function gwom(){
    return $this->getM('WordOrderManager');
  }
  /*-- Redirecting expected functions --*/
  /** @return $study Study */
  public function getStudy(){
    return $this->gsm()->getStudy();
  }
  /** @return $regions Region[] */
  public function getRegions(){
    return $this->grm()->getRegions();
  }
  /** @return $words Word[] */
  public function getWords(){
    return $this->gwm()->getWords();
  }
  /**
    @param study Study
    @return copy CopyValueManager
  */
  public function setStudy($study = null){
    return $this->gsm()->setStudy($study);
  }
  /**
   * @param study Study
   * @return has Bool
   * */
  public function hasStudy($study){
    return $this->gsm()->hasStudy($study);
  }
  /**
    Sets the words without referential transparency.
    @param $words
  */
  public function unsafeSetWords($words = null){
    $this->gwm()->unsafeSetWords($words);
  }
  /**
   * @param words Word[]
   * @return $v ValueManager
   * */
  public function setWords($words = null){
    return $this->gwm()->setWords($words);
  }
  /**
   * @param word Word
   * @return $v ValueManager
   * */
  public function setWord($word = null){
    return $this->gwm()->setWord($word);
  }
  /**
   * @param $word Word|Word[]
   * @return $v ValueManager
   * */
  public function addWord($word){
    return $this->gwm()->addWord($word);
  }
  /**
    Pageview dependent adding of a word.
    @param $word Word
    @return $v ValueManager
  */
  public function pvAddWord($word){
    return $this->gwm()->pvAddWord($word);
  }
  /**
   * If no $word is given hasWord checks if the ValueManager holds any words at all.
   * @param $word Word
   * @return has Bool
   * */
  public function hasWord($word = null){
    return $this->gwm()->hasWord($word);
  }
  /**
    @param $ws Word|Word[]
    @return $v ValueManager
  */
  public function delWord($ws){
    return $this->gwm()->delWord($ws);
  }
  /**
   * @param $regions Region[]
   * @return $v ValueManager
   * */
  public function setRegions($regions = null){
    return $this->grm()->setRegions($regions);
  }
  /**
   * @param $region Region
   * @return $v ValueManager
   * */
  public function setRegion($region = null){
    return $this->grm()->setRegion($region);
  }
  /**
   * @param $region Region
   * @return $v ValueManager
   * */
  public function addRegion($region){
    return $this->grm()->addRegion($region);
  }
  /**
    @param $regions Region[]
    @return $v ValueManager
  */
  public function addRegions($regions){
    return $this->grm()->addRegions($regions);
  }
  /**
   * @param $region Region
   * @return has Bool
   * */
  public function hasRegion($region){
    return $this->grm()->hasRegion($region);
  }
  /**
   * @param $region Region
   * @return $v ValueManager
   * */
  public function delRegion($region){
    return $this->grm()->delRegion($region);
  }
  /**
    @param $regions Region[]
    @return $v ValueManager
  */
  public function delRegions($regions){
    return $this->grm()->delRegions($regions);
  }
  /**
   * @return $languages Language[]
   * */
  public function getLanguages(){
    return $this->glm()->getLanguages();
  }
  /**
    Sets languages without keepin referential transparency.
    @param $languages Language[]
  */
  public function unsafeSetLanguages($languages = null){
    $this->glm()->unsafeSetLanguages($languages);
  }
  /**
   * @param $languages Language[]
   * @return $v ValueManager
   * */
  public function addLanguages($languages){
    return $this->glm()->addLanguages($languages);
  }
  /**
   * @param $language Language
   * @return $v ValueManager
   * */
  public function addLanguage($language){
    return $this->glm()->addLanguage($language);
  }
  /**
   * @param $languages Language[]
   * @return $v ValueManager
   * */
  public function delLanguages($languages){
    return $this->glm()->delLanguages($languages);
  }
  /**
   * @param $language Language
   * @return $v ValueManager
   * */
  public function delLanguage($language){
    return $this->glm()->delLanguage($language);
  }
  /**
   * @param $language Language
   * @return has Bool
   * */
  public function hasLanguage($language){
    return $this->glm()->hasLanguage($language);
  }
  /**
    Tells if there are as many Languages in the current study
    as there are Languages selected.
    @return has Bool
  */
  public function hasAllLanguages(){
    return $this->glm()->hasAllLanguages();
  }
  /**
   * @param $language Language
   * @return $v ValueManager
   * */
  public function setLanguage($language = null){
    return $this->glm()->setLanguage($language);
  }
  /**
   * @param $languages Language[]
   * @return $v ValueManager
   * */
  public function setLanguages($languages = null){
    return $this->glm()->setLanguages($languages);
  }
  /**
   * @param $pageView PageView
   * @return $v ValueManager
   * */
  public function setPageView($pageView = null){
    return $this->gpv()->setPageView($pageView);
  }
  /**
    FIXME check if this function is necessary anymore.
    @return $t TranslationManager
  */
  public function getTranslator(){
    return $this->gtm();
  }
  /**
    @param $mg MeaningGroup
    @return has Bool
  */
  public function hasMeaningGroup($mg){
    return $this->gmgm()->hasMeaningGroup($mg);
  }
  /**
    @param $mg MeaningGroup
    @return $v ValueManager
  */
  public function addMeaningGroup($mg){
    return $this->gmgm()->addMeaningGroup($mg);
  }
  /**
    @param $mg MeaningGroup
    @return $v ValueManager
  */
  public function delMeaningGroup($mg){
    return $this->gmgm()->delMeaningGroup($mg);
  }
  /**
    @param $mg MeaningGroup
    @return $v ValueManager
  */
  public function toggleMeaningGroup($mg){
    return $this->gmgm()->toggleMeaningGroup($mg);
  }
  /**
    @return $v ValueManager
  */
  public function cleanMeaningGroups(){
    return $this->gmgm()->cleanMeaningGroups();
  }
  /**
    @param $mgs MeaningGroup[]
    @return $v ValueManager
  */
  public function setMeaningGroups($mgs = null){
    return $this->gmgm()->setMeaningGroups($mgs);
  }
  /**
    FIXME check if this function is necessary anymore.
    @return $wo WordOrderManager
  */
  public function getWordOrder(){
    return $this->gwom();
  }
  /**
    FIXME check if this function is necessary anymore.
    @return $wo WordOrderManager
  */
  public function gwo(){
    return $this->gwom();
  }
  /**
    Returns the file extensions for soundfiles in transcriptions.
    These might be '.mp3' or '.ogg' for example,
    and are choosen depending on the Useragent.
    @return String[] soundFiles
  */
  public function getSoundFiles(){
    return $this->gsfm()->soundFiles;
  }
  /**
    Marks this ValueManager as UserCleaned.
    Does not keep referential transparency.
    @return $this ValueManager
  */
  public function setUserCleaned(){
    return $this->gucm()->setUserCleaned();
  }
  /***/
  public function unsetUserCleaned(){
    return $this->gucm()->unsetUserCleaned();
  }
  /** @return $userCleaned Bool */
  public function isUserCleaned(){
    return $this->gucm()->isUserCleaned();
  }
}

?>
