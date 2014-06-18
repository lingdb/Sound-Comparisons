<?php
require_once 'SubManager.php';

abstract class TranslationManager extends SubManager{
  protected $defaultTranslationId = 1; //Some default like English (US)
  /**
    The LanguageId of the language into which we shall translate.
  */
  protected $translationId = null;
  /**
    @return $translationId Int
  */
  public function getTarget(){
    return $this->translationId;
  }
  /*
    @return $browserMatch String
  */
  public function getBrowserMatch(){
    $q = "SELECT BrowserMatch FROM Page_Translations WHERE TranslationId = ".$this->getTarget();
    if($r = Config::getConnection()->query($q)->fetch_row())
      return $r[0];
    return null;
  }
  /**
    @param $id Int
    @return $v ValueManager
  */
  public function setTarget($id){
    $t = clone $this;
    $t->translationId = $id;
    return $this->gvm()->setM($t);
  }
  /**
    Tries to translate a request via the static table.
    @param $req String
    @param $tid [translationId] - Used with internal calls.
    @return $trans String
  */
  public function staticTranslate($req, $tid = null){
    if(!isset($tid))
      $tid = $this->translationId;
    //Typical static translation:
    $query = "SELECT Trans FROM Page_StaticTranslation "
           . "WHERE TranslationId = $tid AND Req='$req'";
    $set = Config::getConnection()->query($query);
    if($r = $set->fetch_assoc()){
      return preg_replace('/\<br\>/', "\n", $r['Trans']);
    }
    //Fallback on default if necessary:
    if($tid != $this->defaultTranslationId){
      return $this->staticTranslate($req, $this->defaultTranslationId);
    }
    //Final Fail
    return "MissingStaticTranslation(".$this->translationId.",$req)";
  }
  /**
    This function is shorthand for staticTranslate.
    It also replaces given $1..$n with Strings from the $replacements.
    @param $req String
    @param $replacements String[]
    @return String
  */
  public function st($req, $replacements = array()){
    $ret = $this->staticTranslate($req);
    $count = 1;
    foreach($replacements as $rep){
      $ret = preg_replace("/\\\$$count/", $rep, $ret);
      $count++;
    }
    return $ret;
  }
  /**
    @param $study [Study]
    @param $tid [translationId] - used for internal calls
    @return String pagetitle
  */
  public function getPageTitle($study = null, $tid = null){
    if($study){
      $key = $study->getKey();
      if(!isset($tid))
        $tid = $this->translationId;
      if($r = $study->translate(array('tId' => $tid))){
        $p = $this->st('website_title_prefix');
        $r = $r[0];
        $s = $this->st('website_title_suffix');
        return "$p $r $s";
      }
      //Fallback on default:
      if($tid != $this->defaultTranslationId){
        return $this->getPageTitle($study, $this->defaultTranslationId);
      }
    }
    return $this->st('website_title_prefix');
  }
  /**
    @param $word Word
    @return String translation, null if no translation is found.
  */
  public function getWordTranslation($word){
    $options = array(
      'tId' => $this->translationId
    , 'column' => 'FullRfcModernLg01'
    );
    if($r = $word->translate($options))
      return $r[0];
    return null;
  }
  /**
    @param $word Word
    @return String translation, null if no translation is found.
  */
  public function getWordLongTranslation($word){
    $options = array(
      'tId' => $this->translationId
    , 'column' => 'LongerRfcModernLg01'
    );
    if($r = $word->translate($options))
      return $r[0];
    return null;
  }
  /**
    The returned $translation comes with several fields:
      'ShortName', 'SpellingRfcLangName'
      , 'SpecificLanguageVarietyName'
      , 'RegionGpMemberLgNameLongInThisSubFamilyWebsite'
    , and is an empty array if no translation is found.
    @param $language Language
    @return $translation String[]
  */
  public function getLanguageTranslation($language){
    $ret = array();
    $sid = $this->gvm()->getStudy()->getKey();
    $id  = $language->getId();
    $t   = $this->translationId;
    $field  = $sid.'-'.$id;
    $prefix = 'RegionLanguagesTranslationProvider-RegionLanguages_-Trans_';
    $cols   = array( 'RegionGpMemberLgNameShortInThisSubFamilyWebsite'
                   , 'RegionGpMemberLgNameLongInThisSubFamilyWebsite');
    foreach($cols as $col){
      if($r = Translatable::getTrans($t, $prefix.$col, $field))
        $ret[$col] = $r[0];
    }
    foreach(array( 'ShortName'
                 , 'SpellingRfcLangName'
                 , 'SpecificLanguageVarietyName'
    ) as $col){
      $options = array('tId' => $this->translationId, 'column' => $col);
      if($r = $language->translate($options))
        $ret[$col] = $r[0];
    }
    return $ret;
  }
  /**
    The returned $translation comes with several fields:
      0 => Status, 1 => StatusTooltip, 2 => Description
    , and is null if no translation is found.
    @param $language Language
    @return $translation String[]
  */
  public function getLanguageStatusTypeTranslation($language){
    $ret  = array();
    $tId  = $this->translationId;
    $cols = array('Status', 'StatusTooltip', 'Description');
    $prefix = 'LanguageStatusTypesTranslationProvider-LanguageStatusTypes-Trans_';
    $field  = $language->getLanguageStatusType();
    if($field){
      $field = $field[0];
    }else return null;
    $ok = false;
    foreach($cols as $col){
      if($r = Translatable::getTrans($tId, $prefix.$col, $field)){
        $ok = true;
        array_push($ret, $r[0]);
      }else array_push($ret, '');
    }
    if($ok) return $ret;
    return null;
  }
  /**
    @param $meaningGroup MeaningGroup
    @return $translation [String]
  */
  public function getMeaningGroupTranslation($meaningGroup){
    $ops = array('tId' => $this->translationId);
    if($r = $meaningGroup->translate($ops))
      return $r[0];
    return null;
  }
  /**
    @param $region Region
    @return $translation [String]
  */
  public function getRegionTranslation($region){
    $tId  = $this->translationId;
    $cols = array('RegionGpNameShort', 'RegionGpNameLong');
    $ret  = array();
    foreach($cols as $col){
      $ops = array('tId' => $tId, 'column' => $col);
      if($r = $region->translate($ops))
        array_push($ret, $r[0]);
    }
    return (count($ret) > 0) ? $ret : null;
  }
  /**
    @param $study Study
    @return $translation [String]
  */
  public function getStudyTranslation($study){
    $ops  = array('tId' => $this->translationId);
    if($r = $study->translate($ops))
      return $r[0];
    return null;
  }
  /**
    @param $family Family
    @return $translation [String]
  */
  public function getFamilyTranslation($family){
    $ops  = array('tId' => $this->translationId);
    if($r = $family->translate($ops))
      return $r[0];
    return null;
  }
  /**
    @param $ix Either ISOCode Integer
    If $ix is an ISOCode, the lookup is performed for ISOCodes,
    otherwise it is done for simple TranscriptionSuperscripts.
    @return $translation [$string]
  */
  public function getTranscrSuperscriptTranslation($ix){
    $tId = $this->translationId;
    if(is_numeric($ix)){
      $prefix = 'TranscrSuperscriptInfoTranslationProvider-TranscrSuperscriptInfo-Trans_';
      $cols   = array('Abbreviation', 'HoverText');
      $ret    = array();
      foreach($cols as $col){
        if($r = Translatable::getTrans($tId, $prefix.$col, $ix)){
          array_push($ret, $r[0]);
        }else array_push($ret, '');
      }
      return $ret;
    }else if(strlen($ix) === 3){
      $prefix = 'TranscrSuperscriptLenderLgsTranslationProvider-TranscrSuperscriptLenderLgs-Trans_';
      $cols   = array('Abbreviation', 'FullNameForHoverText');
      $ret    = array();
      foreach($cols as $col){
        if($r = Translatable::getTrans($tId, $prefix.$col, $ix)){
          array_push($ret, $r[0]);
        }else array_push($ret, '');
      }
      return $ret;
    }
    return null;
  }
  /**
    Returns the rfcLanguage linked to the current translation or null.
    @returns rfcLanguage Language
  */
  public function getRfcLanguage(){
    $tid  = $this->translationId;
    $sid  = $this->gvm()->getStudy()->getKey();
    $q = "SELECT RfcLanguage FROM Page_Translations "
       . "WHERE TranslationId = $tid "
       . "AND RfcLanguage = ANY (SELECT LanguageIx FROM Languages_$sid)";
    if($r = Config::getConnection()->query($q)->fetch_row()){
      return new LanguageFromId($this->gvm(), $r[0]);
    }
    return null;
  }
  /**
    @return $flag HTML
  */
  public function showFlag(){
    $query = 'SELECT ImagePath FROM Page_Translations WHERE TranslationId = '.$this->translationId;
    if($r = Config::getConnection()->query($query)->fetch_assoc())
      return $r['ImagePath'];
    return null;
  }
  /**
    @param [$useRfcLanguage = false] Bool
    @return $translationName String
    If $useRfcLanguage showName will return the spellingName
    of the RfcLanguage of the current translation if that is found.
  */
  public function showName($useRfcLanguage = false){
    if($useRfcLanguage)
      if($r = $this->getRfcLanguage())
        return $r->getSpellingName();
    $query = 'SELECT TranslationName FROM Page_Translations WHERE TranslationId = '.$this->translationId;
    if($r = Config::getConnection()->query($query)->fetch_assoc())
      return $r['TranslationName'];
    return '';
  }
  /**
    Fetches all the Translators except the current one
    @returns $others TranslationManager[]
  */
  public function getOthers(){
    $tid = $this->translationId;
    $query = "SELECT TranslationId FROM Page_Translations WHERE Active = 1 AND TranslationId != $tid ORDER BY TranslationName";
    $set = Config::getConnection()->query($query);
    $others = array();
    while($r = $set->fetch_row()){
      $t = clone $this;
      $t->translationId = $r[0];
      array_push($others, $t);
    }
    return $others;
  }
  /** Overwrites Submanager::pack*/
  public function pack(){
    return array('hl' => $this->getBrowserMatch());
  }
  /*-- Shortcuts below --*/
  /**
    dt is short for dynamicTranslation
    @param mixed Study|Word|Language|MeaningGroup
    @return $translation [String]
  */
  public function dt($mixed){
    if($mixed instanceof Study)
      return $this->getStudyTranslation($mixed);
    if($mixed instanceof Word)
      return $this->getWordTranslation($mixed);
    if($mixed instanceof Language)
      return $this->getLanguageTranslation($mixed);
    if($mixed instanceof MeaningGroup)
      return $this->getMeaningGroupTranslation($mixed);
    if($mixed instanceof Region)
      return $this->getRegionTranslation($mixed);
    if($mixed instanceof Family)
      return $this->getFamilyTranslation($mixed);
    return null;
  }
}

class InitTranslationManager extends TranslationManager{
  /**
    Figures out the translationId.
    Decision is taken as follows:
      1: Is there already info in $_GET?
      2: Negotiate the clients preferred language
      3: Fallback to default to allways have a target
    @param $v ValueManager
  */
  public function __construct($v){
    $this->setValueManager($v);
    $db = Config::getConnection();
    //Phase 1:
    if(isset($_GET['hl'])){ // hl as in host language.
      $hl = $db->escape_string($_GET['hl']);
      $q = "SELECT TranslationId FROM Page_Translations WHERE BrowserMatch = '$hl'";
      if($r = $db->query($q)->fetch_row()){
        $this->translationId = $r[0];
        return;
      }
    }
    //Phase 2:
    if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
      $set = $db->query('SELECT TranslationId, BrowserMatch FROM Page_Translations WHERE Active = 1');
      while($row = $set->fetch_assoc())
        if(preg_match('/'.$row['BrowserMatch'].'/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'])){
          $this->translationId = $row['TranslationId'];
          return;
        }
    }
    //Phase 3:
    $this->translationId = $this->defaultTranslationId;
  }
  /***/
  public function getName(){return "TranslationManager";}
}
?>
