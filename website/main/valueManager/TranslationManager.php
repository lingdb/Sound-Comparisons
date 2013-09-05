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
    if($r = mysql_fetch_row(mysql_query($q, $this->getConnection())))
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
    $set = mysql_query($query, $this->getConnection());
    if($r = mysql_fetch_assoc($set))
      return $r['Trans'];
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
      $q = "SELECT Trans FROM Page_DynamicTranslation_StudyTitle "
         . "WHERE StudyName = '$key' AND TranslationId = $tid";
      if($r = mysql_fetch_row(mysql_query($q, $this->getConnection()))){
        $p = $this->st('website_title_prefix');
        $r = $r[0];
        $s = $this->st('website_title_suffix');
        return "$p $r $s";
      }
      //Fallback on default:
      if($tid != $this->defaultTranslationId){
        return $this->getPageTitle($study, $this->defaultTranslationId);
      }
      return $this->st('website_title_prefix');
    }
  }
  /**
    @param $word Word
    @return String translation, null if no translation is found.
  */
  public function getWordTranslation($word){
    $id  = $word->getId();
    $sid = $this->gvm()->getStudy()->getKey();
    $t   = $this->translationId;
    $q   = "SELECT Trans_FullRfcModernLg01 FROM Page_DynamicTranslation_Words "
         . "WHERE TranslationId = $t AND Study = '$sid' "
         . "AND CONCAT(IxElicitation, IxMorphologicalInstance) = $id";
    if($r = mysql_fetch_row(mysql_query($q, $this->getConnection())))
      return $r[0];
    return null;
  }
  /**
    The returned $translation comes with several fields:
      'ShortName', 'SpellingRfcLangName'
      , 'SpecificLanguageVarietyName'
      , 'RegionGpMemberLgNameLongInThisSubFamilyWebsite'
    , and is null if no translation is found.
    @param $language Language
    @return $translation String[]
  */
  public function getLanguageTranslation($language){
    $ret = array();
    $sid = $this->gvm()->getStudy()->getKey();
    $id  = $language->getId();
    $t   = $this->translationId;
    $q   = "SELECT Trans_RegionGpMemberLgNameShortInThisSubFamilyWebsite"
         . ", Trans_RegionGpMemberLgNameLongInThisSubFamilyWebsite "
         . "FROM Page_DynamicTranslation_RegionLanguages "
         . "WHERE TranslationId = $t "
         . "AND Study = '$sid' "
         . "AND LanguageIx = $id";
    if($r = mysql_fetch_row(mysql_query($q, $this->getConnection()))){
      $ret['RegionGpMemberLgNameShortInThisSubFamilyWebsite'] = $r[0];
      $ret['RegionGpMemberLgNameLongInThisSubFamilyWebsite']  = $r[1];
    }
    $q   = "SELECT Trans_ShortName, Trans_SpellingRfcLangName, Trans_SpecificLanguageVarietyName "
         . "FROM Page_DynamicTranslation_Languages "
         . "WHERE TranslationId = $t AND LanguageIx = $id AND Study = '$sid'";
    if($r = mysql_fetch_row(mysql_query($q, $this->getConnection()))){
      $ret['ShortName']                   = $r[0];
      $ret['SpellingRfcLangName']         = $r[1];
      $ret['SpecificLanguageVarietyName'] = $r[2];
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
    $id = $language->getId();
    $t  = $this->translationId;
    $sk = $language->getStudy()->getKey();
    $q  = "SELECT Trans_Status, Trans_Description, Trans_StatusTooltip "
        . "FROM Page_DynamicTranslation_LanguageStatusTypes "
        . "WHERE TranslationId = $t "
        . "AND LanguageStatusType = "
        . "(SELECT LanguageStatusType FROM Languages_$sk "
        . "WHERE LanguageIx = $id)";
    if($r = mysql_fetch_row(mysql_query($q, $this->getConnection()))){
      return $r;
    }
    return null;
  }
  /**
    @param $meaningGroup MeaningGroup
    @return $translation [String]
  */
  public function getMeaningGroupTranslation($meaningGroup){
    $id = $meaningGroup->getId();
    $t  = $this->translationId;
    $q  = "SELECT Trans FROM Page_DynamicTranslation_MeaningGroups "
        . "WHERE TranslationId = $t AND MeaningGroupIx = $id";
    if($r = mysql_fetch_row(mysql_query($q, $this->getConnection())))
      return $r[0];
    return null;
  }
  /**
    @param $region Region
    @return $translation [String]
  */
  public function getRegionTranslation($region){
    $id  = $region->getId();
    $t   = $this->translationId;
    $sid = $this->gvm()->getStudy()->getKey();
    $q   = "SELECT Trans_RegionGpNameShort, Trans_RegionGpNameLong FROM Page_DynamicTranslation_Regions WHERE "
         . "TranslationId = $t AND Study = '$sid' AND RegionIdentifier = '$id'";
    if($r = mysql_fetch_row(mysql_query($q, $this->getConnection()))){
      return $r;
    }
    return null;
  }
  /**
    @param $study Study
    @return $translation [String]
  */
  public function getStudyTranslation($study){
    $id = $study->getKey();
    $t  = $this->translationId;
    $q  = "SELECT Trans FROM Page_DynamicTranslation_Studies WHERE TranslationId = $t AND Study = '$id'";
    if($r = mysql_fetch_row(mysql_query($q, $this->getConnection()))){
      return $r[0];
    }
    return null;
  }
  /**
    @param $family Family
    @return $translation [String]
  */
  public function getFamilyTranslation($family){
    $id = $family->getId();
    $t  = $this->translationId;
    $q  = "SELECT Trans FROM Page_DynamicTranslation_Families "
        . "WHERE CONCAT(StudyIx, FamilyIx) = $id AND TranslationId = $t";
    if($r = mysql_fetch_row(mysql_query($q, $this->getConnection()))){
      return $r[0];
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
    if($r = mysql_fetch_row(mysql_query($q, $this->getConnection()))){
      return new LanguageFromId($this->gvm(), $r[0]);
    }
    return null;
  }
  /**
    @return $flag HTML
  */
  public function showFlag(){
    $query = 'SELECT ImagePath FROM Page_Translations WHERE TranslationId = '.$this->translationId;
    if($r = mysql_fetch_assoc(mysql_query($query, $this->getConnection())))
      return "<img class='flag' src='".$r['ImagePath']."' />";
    return '';
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
    if($r = mysql_fetch_assoc(mysql_query($query, $this->getConnection())))
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
    $set = mysql_query($query, $this->getConnection());
    $others = array();
    while($r = mysql_fetch_row($set)){
      $t = clone $this;
      $t->translationId = $r[0];
      array_push($others, $t);
    }
    return $others;
  }
  /** Overwrites Submanager::pack*/
  public function pack(){
    return array('translator' => $this->getTarget());
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
    //Phase 1:
    if(isset($_GET['translator'])){
      $this->translationId = mysql_real_escape_string($_GET['translator']);
      //Check if result is in table
      $query = "SELECT * FROM Page_Translations WHERE TranslationId = "
        .$this->translationId;
      $chk = mysql_num_rows(mysql_query($query,$this->getConnection()));
      if($chk > 0)//Found a valid translation
        return;
    }
    //Phase 2:
    if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
      $set = mysql_query('SELECT TranslationId, BrowserMatch FROM Page_Translations WHERE Active = 1', $this->getConnection());
      while($row = mysql_fetch_assoc($set))
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
