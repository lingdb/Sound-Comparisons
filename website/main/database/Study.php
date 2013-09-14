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
    $set = $this->dbConnection->query($q);
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
    $set = $this->dbConnection->query($q);
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
    $set = $this->dbConnection->query($q);
    $ret = array();
    while($r = $set->fetch_row()){
      $l = new LanguageFromId($this->v, $r[0]);
      if($l->hasTranscriptions() || $allowNoTranscriptions)
        array_push($ret, $l);
    }
    return $ret;
  }
  /**
    @return $meaningGroups MeaningGroup[]
    Returns all MeaningGroups that occur for one or more Words in a Study.
  */
  public function getMeaningGroups(){
    $id = $this->id;
    $q = "SELECT DISTINCT MeaningGroupIx "
       . "FROM MeaningGroupMembers "
       . "WHERE CONCAT(StudyIx, FamilyIx) "
       . "LIKE (SELECT CONCAT(REPLACE(CONCAT(StudyIx, FamilyIx),0,''),'%') "
       . "FROM Studies WHERE Name = '$id')";
    $set = $this->dbConnection->query($q);
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
    Words are sortet by the given ValueManager,
    or by the ValueManager that the Study was created with.
  */
  public function getWords($v = null){
    if($v == null)
      $v  = $this->getValueManager();
    $id   = $this->id;
    $q = "SELECT CONCAT(IxElicitation, IxMorphologicalInstance) FROM Words_$id";
    if($v->gwo()->isAlphabetical()){
      if($spl = $v->gwo()->getSpLang()){
        if($spl->getStudy()->getId() == $id){
          $rfcId = $spl->getId();
          $q = "SELECT DISTINCT CONCAT(IxElicitation, IxMorphologicalInstance) "
             . "FROM Words_$id JOIN Transcriptions_$id USING (IxElicitation, IxMorphologicalInstance) "
             . "WHERE LanguageIx = $rfcId "
             . "ORDER BY SpellingAltv1, SpellingAltv2, FullRfcModernLg01";
        }
      }else{
        $tid = $v->gtm()->getTarget();
        /**
          We select all the translated words,
          but make sure to fill them up with the distinct
          untranslated ones.
          This way if only some words have been translated,
          or none at all, we still get to see them.
        */
        $q = "SELECT DISTINCT CONCAT(IxElicitation, IxMorphologicalInstance) "
           . "FROM Page_DynamicTranslation_Words "
           . "WHERE TranslationId = $tid "
           . "AND Study = '$id' "
           . "UNION DISTINCT "
           . "SELECT DISTINCT CONCAT(IxElicitation, IxMorphologicalInstance) "
           . "FROM Words_$id";
      }
    }
    $words = $this->dbConnection->query($q);
    $ret = array();
    while($r = $words->fetch_row())
      array_push($ret, new WordFromId($this->v, $r[0]));
    return $ret;
  }
  /***/
  public function getColorByFamily(){
    $id = $this->id;
    $q  = "SELECT ColorByFamily FROM Studies WHERE Name = '$id'";
    $r  = $this->dbConnection->query($q)->fetch_row();
    return ($r[0] == 1);
  }
  /***/
  public function getFamilies(){
    $id   = $this->id;
    $q    = "SELECT CONCAT(StudyIx, FamilyIx) FROM Families "
          . "WHERE CONCAT(StudyIx, FamilyIx) LIKE ("
          . "SELECT CONCAT(StudyIx, REPLACE(FamilyIx, 0, ''), '%') "
          . "FROM Studies WHERE Name = '$id')";
    $set  = $this->dbConnection->query($q);
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
    if($r = $this->dbConnection->query($q)->fetch_row()){
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
    $r = $this->dbConnection->query($q)->fetch_row();
    if($r[0] >= 1){
      $this->id = $key; // id == key
    }
    else die("Invalid StudyName: $key");
  }
}
?>
