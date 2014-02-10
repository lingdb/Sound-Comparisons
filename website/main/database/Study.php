<?php
require_once 'DBEntry.php';
require_once 'MeaningGroups.php';
/**
  An entry of the Studies table.
  The special thing about a Study is that it's only
  useful key attribute is it's Name.
  So the name becomes key and id.
*/
class Study extends DBEntry{
  /**
    @param [$v] ValueManager
    @return $name String
    If a ValueManager is given, the Study will
    attempt to return it's name in the current Translation.
  */
  public function getName($v = null){
    if($v){
      if($name = $v->getTranslator()->getStudyTranslation($this)){
        return $name;
      }
    }
    return $this->key;
  }
  /**
    @return $regions Region[]
    Returns an Array of all Regions in a Study.
  */
  public function getRegions(){
    $id = $this->id;
    $q = "SELECT CONCAT(StudyIx, FamilyIx, SubFamilyIx, RegionGpIx) FROM Regions_$id";
    $set = Config::getConnection()->query($q);
    $ret = array();
    while($r = $set->fetch_row())
      array_push($ret, new RegionFromId($this->getValueManager(), $r[0]));
    return $ret;
  }
  /**
    @return $languages Language[]
    Returns an array of all Spelling languages in the study.
  */
  public function getSpellingLanguages(){
    $id = $this->id;
    $q = "SELECT LanguageIx FROM Languages_$id WHERE IsSpellingRfcLang = 1";
    $set = Config::getConnection()->query($q);
    $ret = array();
    while($r = $set->fetch_row()){
      array_push($ret, new LanguageFromId($this->v, $r[0]));
    }
    return $ret;
  }
  /**
    @param [$allowNoTranscriptions = false]
    @return $languages language[]
    Returns all languages in a Study
  */
  public function getLanguages($allowNoTranscriptions = false){
    $id = $this->id;
    $q  = "SELECT LanguageIx FROM RegionLanguages_$id";
    $set = Config::getConnection()->query($q);
    $ret = array();
    while($r = $set->fetch_row()){
      $l = new LanguageFromId($this->v, $r[0]);
      if($l->hasTranscriptions() || $allowNoTranscriptions)
        array_push($ret, $l);
    }
    return $ret;
  }
  /***/
  public function getMapExcludeLanguages(){
    $id = $this->id;
    $q  = "SELECT LanguageIx FROM Default_Languages_Exclude_Map "
        . "WHERE CONCAT(StudyIx, FamilyIx) LIKE ("
          . "SELECT CONCAT(StudyIx, REPLACE(FamilyIx, 0, '%')) FROM Studies WHERE Name = '$id'"
        . ") AND LanguageIx = ANY (SELECT LanguageIx FROM Languages_$id)";
    $set = Config::getConnection()->query($q);
    $ret = array();
    while($r = $set->fetch_row())
      array_push($ret, new LanguageFromId($this->v, $r[0]));
    return $ret;
  }
  /**
    @return $meaningGroups MeaningGroup[]
    Returns all MeaningGroups that occur for one or more Words in a Study.
  */
  public function getMeaningGroups(){
    $id = $this->id;
    $q = "SELECT StudyIx, FamilyIx FROM Studies WHERE Name = '$id'";
    $r = Config::getConnection()->query($q)->fetch_row();
    $sIx = $r[0]; $fIx = $r[1];
    $q = "SELECT DISTINCT MeaningGroupIX "
       . "FROM MeaningGroupMembers "
       . "WHERE StudyIx = $sIx AND "
       . "(FamilyIx = 0 OR FamilyIx = $fIx)";
    $set = Config::getConnection()->query($q);
    $ret = array();
    while($r = $set->fetch_row()){
      array_push($ret, new MeaningGroupFromId($this->getValueManager(), $r[0]));
    }
    return $ret;
  }
  /**
    @param [$v] ValueManager
    @return $words Word[]
    Returns all Words in a Study.
    Words are sorted by the given ValueManager,
    or by the ValueManager that the Study was created with.
  */
  public function getWords($v = null){
    //Fetching words:
    $id = $this->id;
    $q = "SELECT CONCAT(IxElicitation, IxMorphologicalInstance) FROM Words_$id";
    $set = Config::getConnection()->query($q);
    $words = array();
    while($r = $set->fetch_row()){
      array_push($words, new WordFromId($this->v, $r[0]));
    }
    //Sorting words:
    $v = is_null($v) ? $this->getValueManager() : $v;
    if($v->gwo()->isAlphabetical()){
      usort($words, 'Word::compareOnTranslation');
    }else{
      usort($words, 'DBEntry::compareOnId');
    }
    //Done:
    return $words;
  }
  /***/
  public function getColorByFamily(){
    $id = $this->id;
    $q  = "SELECT ColorByFamily FROM Studies WHERE Name = '$id'";
    $r  = Config::getConnection()->query($q)->fetch_row();
    return ($r[0] == 1);
  }
  /***/
  public function getFamilies(){
    $id   = $this->id;
    $q    = "SELECT CONCAT(StudyIx, FamilyIx) FROM Families "
          . "WHERE CONCAT(StudyIx, FamilyIx) LIKE ("
          . "SELECT CONCAT(StudyIx, REPLACE(FamilyIx, 0, ''), '%') "
          . "FROM Studies WHERE Name = '$id')";
    $set  = Config::getConnection()->query($q);
    $fams = array();
    while($r = $set->fetch_row()){
      array_push($fams, new FamilyFromId($this->v, $r[0]));
    }
    return $fams;
  }
  /**
    @return [corners] Array[]
    Returns the corners which define how the
    MapsView should be zoomed per default.
    The default zoom is defined by coordinates
    for two corners, the upper left and the lower right one.
    The result is an array of two arrays mapping the keys lat and lon to values.
    Or, if the Corners are invalid values, the result is null.
    This way, getMapZoomCorners can be easily converted to json
    which is necessary to use it in MapView.
  */
  public function getMapZoomCorners(){
    $key = $this->key;
    $q = "SELECT DefaultTopLeftLat, DefaultTopLeftLon, "
       . "DefaultBottomRightLat, DefaultBottomRightLon "
       . "FROM Studies WHERE Name = '$key'";
    if($r = Config::getConnection()->query($q)->fetch_row()){
      $valid = true;
      foreach($r as $v)
        if($v === null || $v === 0){
          $valid = false;
          break;
        }
      if($valid)
        return array(
            array('lat' => $r[0],'lon' => $r[1])
          , array('lat' => $r[2],'lon' => $r[3])
          );
    }
    return null;
  }
  /**
    @return defaults Language[]
    Tries to fetch entries from Default_Multiple_Languages.
    If no defaults could be found, we fall back to the first 5
    in the study.
  */
  public function getDefaultLanguages(){
    $v  = RedirectingValueManager::getInstance();
    $id = $this->id;
    $q  = "SELECT LanguageIx FROM Default_Multiple_Languages "
        . "WHERE CONCAT(StudyIx, FamilyIx) LIKE ("
          . "SELECT CONCAT(StudyIx, REPLACE(FamilyIx, 0, '%')) FROM Studies WHERE Name = '$id'"
        . ") AND LanguageIx = ANY (SELECT LanguageIx FROM Languages_$id)";
    $defaults = $this->fetchAllBy($q);
    if(count($defaults) === 0){
      $q = "SELECT LanguageIx FROM Languages_$id LIMIT 5";
      $defaults = $this->fetchAllBy($q);
    }
    return __($defaults)->map(function($r) use ($v){
      return new LanguageFromId($v, $r[0]);
    });
  }
  /**
    @return default Language
    Tries to fetch entry from Default_Languages.
    If no default could be found, we fall back to the first in the study.
  */
  public function getDefaultLanguage(){
    $v  = RedirectingValueManager::getInstance();
    $id = $this->id;
    $q  = "SELECT D.LanguageIx FROM Default_Languages AS D "
        . "JOIN Studies AS S USING (StudyIx, FamilyIx) "
        . "WHERE S.Name = '$id'";
    $default = $this->fetchOneBy($q);
    if(!$default){
      $q = "SELECT LanguageIx FROM Languages_$id "
         . "ORDER BY LanguageIx ASC LIMIT 1";
      $default = $this->fetchOneBy($q);
    }
    return new LanguageFromId($v, $default[0]);
  }
  /**
    @return phLang Language
  */
  public function getDefaultPhoneticLanguage(){
    $v  = RedirectingValueManager::getInstance();
    $id = $this->id;
    $q  = "SELECT LanguageIx FROM Languages_$id "
        . "WHERE IsOrthographyHasNoTranscriptions = 0 "
        . "OR IsOrthographyHasNoTranscriptions IS NULL "
        . "LIMIT 1";
    if($r = $this->fetchOneBy($q))
      return new LanguageFromId($v, $r[0]);
    return null;
  }
  /**
    @return defaults Word[]
    Tries to fetch entries from Default_Multiple_Words.
    If no defaults could be found, we use the first 5 for the study.
  */
  public function getDefaultWords(){
    $v  = RedirectingValueManager::getInstance();
    $id = $this->id;
    $q  = "SELECT DISTINCT CONCAT(IxElicitation, IxMorphologicalInstance) "
        . "FROM Default_Multiple_Words WHERE CONCAT(StudyIx, FamilyIx) "
        . "LIKE (SELECT CONCAT(StudyIx,REPLACE(FamilyIx, 0, ''),'%') "
          . "FROM Studies WHERE Name = '$id')";
    $defaults = $this->fetchAllBy($q);
    if(count($defaults) === 0){
      $q = "SELECT CONCAT(IxElicitation, IxMorphologicalInstance) "
         . "FROM Words_$id "
         . "ORDER BY IxElicitation LIMIT 5";
      $defaults = $this->fetchAllBy($q);
    }
    return __($defaults)->map(function($r) use ($v){
      return new WordFromId($v, $r[0]);
    });
  }
  /**
    @return default Word
    Tries to fetch entry from Default_Words.
    If no default could be found, we fall back to the first for the stody.
  */
  public function getDefaultWord(){
    $v  = RedirectingValueManager::getInstance();
    $id = $this->id;
    $q  = "SELECT DISTINCT CONCAT(IxElicitation, IxMorphologicalInstance) "
        . "FROM Default_Words WHERE CONCAT(StudyIx, FamilyIx) "
        . "LIKE (SELECT CONCAT(StudyIx,REPLACE(FamilyIx, 0, ''),'%') "
          . "FROM Studies WHERE Name = '$id')";
    $default = $this->fetchOneBy($q);
    if(!$default){
      $q = "SELECT CONCAT(IxElicitation, IxMorphologicalInstance) "
         . "FROM Words_$id LIMIT 1";
      $default = $this->fetchOneBy($q);
    }
    return new WordFromId($v, $default[0]);
  }
  /***/
  public static function getStudies(){
    $v = RedirectingValueManager::getInstance();
    $studies = array();
    $set = Config::getConnection()->query('SELECT DISTINCT Name FROM Studies');
    while($r = $set->fetch_row()){
      array_push($studies, new StudyFromKey($v, $r[0]));
    }
    return $studies;
  }
}
/** Extends the Study for a constructor to create a Study from a key. */
class StudyFromKey extends Study{
  /**
    @param $v ValueManager
    @param $key String name of the Study in the Studies table
  */
  public function __construct($v, $key){
    $this->setup($v);
    $this->key = $key;
    $q = "SELECT COUNT(*) FROM Studies WHERE Name='$key'";
    $r = Config::getConnection()->query($q)->fetch_row();
    if($r[0] >= 1){
      $this->id = $key; // id == key
    }
    else Config::error("Invalid StudyName: $key");
  }
}
?>
