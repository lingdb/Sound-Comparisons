<?php
require_once 'DBEntry.php';
/**
  Corresponds to an Entry from the Languages table.
*/
class Language extends DBEntry{
  /***/
  protected function buildSelectQuery($fs){
    $sid = $this->getValueManager()->getStudy()->getId();
    $id  = $this->id;
    return "SELECT $fs FROM Languages_$sid WHERE LanguageIx = $id";
  }
  /***/
  protected function findKey(){
    if($r = $this->fetchFields('ShortName')){
      $this->key = $r[0];
    }else die("database/Language.php: No name for Language: ".$this->id);
  }
  /**
    @return [$skey] String
    Produces a key that let's us sort Languages by their regionGroups.
  */
  public function getSortKey(){
    $sid = $this->getValueManager()->getStudy()->getId();
    $id = $this->id;
    $q = "SELECT CONCAT(StudyIx, FamilyIx, SubFamilyIx, RegionGpIx, $id) "
       . "FROM RegionLanguages_$sid WHERE LanguageIx = $id";
    if($r = $this->fetchRow($q))
      return $r[0];
    die("Fail in database/Language->getSortKey() for LanguageIx $id");
  }
  /**
    @param [$superscript=true] Bool
    @return $shortName String
    The name of a language ready to display,
    translated if possible.
    If $superscript is true, the $shortName is returned
    as HTML with a superscript that characterises the Language.
  */
  public function getShortName($superscript = true){
    $id   = $this->id;
    $sid  = $this->getValueManager()->getStudy()->getId();
    $name = $this->getKey(); // Fallback if no ShortName is found
    //Fetchig the ShortName field:
    if($r = $this->fetchFields('ShortName'))
      $name = $r[0];
    //Fetching a translation if possible:
    if($trans = $this->getValueManager()->getTranslator()->dt($this)){
      foreach(array('RegionGpMemberLgNameShortInThisSubFamilyWebsite'
                   ,'RegionGpMemberLgNameLongInThisSubFamilyWebsite'
                   ,'ShortName'
                   ) as $k){
        if(array_key_exists($k, $trans)){
          $v = $trans[$k];
          if($v != ''){
            $name = $v;
            break;
          }
        }
      }
    }
    //Fetching the superscript if requested:
    if($superscript)
      $name = $this->getSuperscript($name);
    return $name;
  }
  /**
    @param [$superscript=true] Bool
    @return $longName String
    Works like getShortName except that the $longName is richer on details.
  */
  public function getLongName($superscript = true){
    //Try to find a translation first:
    if($trans = $this->getValueManager()->getTranslator()->dt($this)){
      $k = 'RegionGpMemberLgNameLongInThisSubFamilyWebsite';
      if(array_key_exists($k, $trans)){
        $trans = $trans[$k];
        if($trans !== ''){
          if($superscript) $trans = $this->getSuperscript($trans);
          return $trans;
        }
      }
    }//Try to find a long name:
    $sid = $this->getValueManager()->getStudy()->getId();
    $id  = $this->id;
    $q = "SELECT RegionGpMemberLgNameLongInThisSubFamilyWebsite FROM RegionLanguages_$sid "
       . "WHERE RegionGpMemberLgNameLongInThisSubFamilyWebsite IS NOT NULL "
       . "AND RegionGpMemberLgNameLongInThisSubFamilyWebsite != '' "
       . "AND LanguageIx = $id";
    if($r = $this->dbConnection->query($q)->fetch_row()){
      $lname = $r[0];
      if($superscript)
        $lname = $this->getSuperscript($lname);
      return $lname;
    }//Fallback on shortName:
    return $this->getShortName($superscript);
  }
  /**
    @return $spellingName String
    Returns the SpellingRfcLangName from the Languages table.
  */
  public function getSpellingName(){
    if($t = $this->getValueManager()->getTranslator()->dt($this)){
      $t = $t['SpellingRfcLangName'];
      if($t != '') return $t;
    }
    if($r = $this->fetchFields('SpellingRfcLangName')){
      if($r[0]) return $r[0];
    }
    return $this->getShortName(false);
  }
  /**
    @param [$target] String that the superscript belongs to.
    @return superscript String - a HTML div element.
    Generates a superscript that can be attached to a given target,
    or may be used as 'stand alone' if $target is null.
  */
  public function getSuperscript($target = null){
    $s = '';
    $ttip = '';
    $sid = $this->getValueManager()->getStudy()->getId();
    $id = $this->id;
    $q = "SELECT Status, StatusTooltip FROM LanguageStatusTypes "
       . "WHERE LanguageStatusType = (SELECT LanguageStatusType FROM Languages_$sid WHERE LanguageIx = $id) "
       . "AND Status IS NOT NULL AND StatusTooltip IS NOT NULL";
    if($r = $this->fetchRow($q)){
      $s    = $r[0];
      $ttip = $r[1];
    }
    if($trans = $this->getValueManager()->getTranslator()->getLanguageStatusTypeTranslation($this)){
      if($trans[0] != ''){
        $s = $trans[0];
      }
      $ttip = $trans[2];
    }
    if($target === null){
      $s = "<div class='superscriptStandalone' title=\"$ttip\">$s</div>";
    }else{
      $s = "$target<div class='superscript' title=\"$ttip\">$s</div>";
    }
    return $s;
  }
  /**
    @return [$languageStatusType] String
    Returns the LanguageStatusType for a Language.
  */
  public function getLanguageStatusType(){
    $sid = $this->getValueManager()->getStudy()->getId();
    $id  = $this->id;
    $q   = "SELECT LanguageStatusType FROM Languages_$sid WHERE LanguageIx = $id";
    if($r = $this->fetchRow($q)){
      return $r[0];
    }
    return null;
  }
  /**
    @return color String of the form '#xxxxxx' with x being hexadecimal.
    The Languages color can either be determined by the Study it belongs to,
    or by the LanguageStatusType that it has.
    If neither is found in the database,
    the std. background color of the website is returned.
  */
  public function getColor(){
    $skey = $this->getValueManager()->getStudy()->getKey();
    $sid  = $this->getValueManager()->getStudy()->getId();
    $id   = $this->getId();
    if($this->getValueManager()->getStudy()->getColorByFamily()){
      $q = "SELECT FamilyColorOnWebsite FROM Families WHERE CONCAT(StudyIx, FamilyIx) "
         . "= (SELECT CONCAT(StudyIx, FamilyIx) FROM Languages_$sid WHERE LanguageIx = $id)";
    }else{
      $q = "SELECT Color FROM LanguageStatusTypes WHERE LanguageStatusType = ("
         . "SELECT LanguageStatusType FROM Languages_$sid WHERE LanguageIx = $id)";
    }
    if($r = $this->fetchRow($q)){
      return '#'.$r[0];
    }
    return '#FFFACD';
  }
  /**
    @return [$rfcLanguage] Language
    Fetches the RfcLanguage for a Language if it exists.
  */
  public function getRfcLanguage(){
    $sid = $this->getValueManager()->getStudy()->getId();
    $id  = $this->id;
    $q = "SELECT RfcLanguage FROM Languages_$sid "
       . "WHERE LanguageIx = $id "
       . "AND RfcLanguage = ANY (SELECT LanguageIx FROM Languages_$sid) "
       . "AND RfcLanguage != $id"; // No identities
    if($r = $this->fetchRow($q))
      return new LanguageFromId($this->v, $r[0]);
    return null;
  }
  /**
    @return is Bool
    Tells, if the Language is a ReferenceLanguage.
  */
  public function isRfcLanguage(){
    $sKey = $this->getValueManager()->getStudy()->getKey();
    $id = $this->getId();
    $q = "SELECT COUNT(*) FROM Languages_$sKey WHERE RfcLanguage = $id AND LanguageIx != $id";
    if($r = $this->fetchRow($q))
      return ($r[0] > 0);
    return false;
  }
  /**
    @return $path String
    Tries to select the Language part of a soundfile path.
    If it fails php dies, because that would be a heavy error.
  */
  public function getPath(){
    if($r = $this->fetchFields('FilePathPart'))
      return $r[0];
    else die('No CcdOverallFilename for LanguageId: '.$this->id);
  }
  /**
    @return $regions Region[]
    Returns all Regions a Language belongs to.
  */
  public function getRegions(){
    $sid = $this->getValueManager()->getStudy()->getId();
    $id = $this->id;
    $q = "SELECT CONCAT(StudyIx, FamilyIX, SubFamilyIx, RegionGpIx) "
       . "FROM RegionLanguages_$sid WHERE LanguageIx = $id";
    $set = $this->dbConnection->query($q);
    $ret = array();
    while($row = $set->fetch_row())
      array_push($ret, new RegionFromId($this->v, $row[0]));
    return $ret;
  }
  /**
    @return $region Region
    Fetches only the first Region from this Language
  */
  public function getRegion(){
    $regions = $this->getRegions();
    return array_shift($regions);
  }
  /**
    @param $region Region
    @return $belongs Bool
    Checks if a Language belongs to a given Region.
  */
  public function belongsToRegion($region){
    $sid = $this->getValueManager()->getStudy()->getId();
    $id = $this->id;
    $regionId = $region->getId();
    $q = "SELECT ($id = ANY ("
       . "SELECT LanguageIx FROM RegionLanguages_$sid "
       . "WHERE CONCAT(StudyIx, FamilyIX, SubFamilyIx, RegionGpIx) = $regionId"
       . "))";
    $r = $this->fetchRow($q);
    return ($r[0] == 1);
  }
  /**
    @return $isHistorical Bool
    A Language is Historical if any of it's regions is.
  */
  public function isHistorical(){
    foreach($this->getRegions() as $r)
      if($r->isHistorical())
        return true;
    return false;
  }
  /**
    @return $study Study
    Fetches the Study a language belongs in.
  */
  public function getStudy(){
    //Usual studyselection:
    $id = $this->id;
    $q = "SELECT S.Name FROM Studies as S JOIN RegionLanguages as RL USING (StudyIx, FamilyIx, SubFamilyIx) "
       . "WHERE RL.LanguageIx = $id LIMIT 1";
    if($r = $this->fetchRow($q))
      return new StudyFromKey($this->v, $r[0]);
    //Fallback on selecting study as prefix of id:
    /*
      Fallback on selecting Study as prefix of id:
      In this case the Prefix will be the CONCAT(StudyIx, FamilyIx, SubFamilyIx)
      from the Studies Table except for trailing zeroes which shall
      be interpreted as wildcards.
    */
    $q = "SELECT Name FROM Studies WHERE $id LIKE "
       . "CONCAT(REPLACE(CONCAT(StudyIx, FamilyIx, SubFamilyIx),'0',''),'%') "
       . "ORDER BY CONCAT(StudyIx, FamilyIx, SubFamilyIx) LIMIT 1";
    if($r = $this->fetchRow($q))
      return new StudyFromKey($this->v, $r[0]);
    //No Study found:
    die("No Study found for LanguageIx: $id.");
  }
  /**
    @param [$html=true] Bool - decides if getFlag produces a html img tag or the raw flag url.
    @return $flag String - maybe an empty one
    Builds an img tag for the flag image of a language.
  */
  public function getFlag($html = true){
    $sid = $this->getValueManager()->getStudy()->getId();
    $id = $this->id;
    $q = "SELECT Flag FROM Languages_$sid WHERE LanguageIx = $id AND Flag != '' AND FLAG IS NOT NULL";
    if($r = $this->fetchRow($q)){
      $flag = $r[0];
      $q = "SELECT Tooltip FROM FlagTooltip WHERE Flag = '$flag'";
      $tooltip = '';
      if($r = $this->dbConnection->query($q)->fetch_row())
        $tooltip = $r[0];
      if($html)
        return "<img src='img/flags/$flag.png' title='$tooltip' />";
      return "img/flags/$flag.png";
    }
    return '';
  }
  /**
    @return [$location] Array
    Fetches latitude and longtitude of a Language
    and returns it as an array with two fields.
    array[0] = latitude, array[1] = longtitude
    Returns null if no Location is associated with this language.
  */
  public function getLocation(){
    if($r = $this->fetchFields('Latitude, Longtitude'))
      return $r;
    return null;
  }
  /**
    @param $t Translator
    @return $mapsLink String
    Returns a link to googlemaps, where the languages location is marked.
    Link will be target='_blank'
    If no location is attached, the empty String is returned.
  */
  public function getMapsLink($t){
    $ll = $this->getLocation();
    if(!$ll)
      return '';
    $tooltip = $t->st('tooltip_languages_link_mapview');
    $v = $this->getValueManager();
    $href = 'http://maps.google.com/maps?z=12&q='.implode(',', $ll);
    return "<a href='$href'><img class='favicon' src='img/langmap.png' title='$tooltip' /></a>";
  }
  /**
    @param $t Translator
    @return $wikipediaLink String
    Returns a wikipedia link building upon the IsoData table.
    Fail will produce an empty String.
  */
  public function getWikipediaLink($t){
    $tooltip = $t->st('tooltip_languages_link_wikipedia');
    $bm  = $this->getValueManager()->gtm()->getBrowserMatch();
    if($r = $this->fetchFields('ISOCode','WikipediaLinkPart')){
      $iso   = $r[0];
      $wpart = $r[1];
      $q = "SELECT Href FROM WikipediaLinks "
         . "WHERE BrowserMatch = '$bm' "
         . "AND ISOCode = '$iso' "
         . "AND WikipediaLinkPart = '$wpart'";
      if($r = $this->fetchRow($q))
        return "<a href='".$r[0]."' target='_blank'>"
             . "<img class='favicon favicon-bordered' "
             . "src='http://en.wikipedia.org/favicon.ico' title='$tooltip' /></a>";
    }
    return '';
  }
  /**
    @param $t Translator
    @return $links String
    Works similar to the wikipedia one.
  */
  public function getVariousLinks($t){
    $ret = '';
    $sid = $this->getValueManager()->getStudy()->getId();
    $id = $this->id;
    $q = "SELECT ISOCode FROM Languages_$sid "
       . "WHERE LanguageIx = $id AND ISOCode != ''";
    if($r = $this->fetchRow($q)){
      $iso = $r[0];
      //Enthnologue:
      $tooltip = $t->st('tooltip_languages_link_ethnologue');
      $ret = "$ret<a href='http://www.ethnologue.com/show_language.asp?code=$iso' target='_blank'><img class='favicon' src='http://www.ethnologue.com/favicon.ico' title='$tooltip' /></a>";
      //Glottolog:
      $tooltip = $t->st('tooltip_languages_link_glottolog');
      $ret = "$ret<a href='http://www.glottolog.org/resource/languoid/iso/$iso' target='_blank'><img class='favicon' src='img/extern/glottolog.png' title='$tooltip'/></a>";
      //Multitree:
      $tooltip = $t->st('tooltip_languages_link_multitree');
      $ret = "$ret<a href='http://multitree.org/codes/$iso.html' target='_blank'><img class='favicon' src='http://multitree.org/images/favicon.ico' title='$tooltip'/></a>";
      //LLMap:
      $tooltip = $t->st('tooltip_languages_link_llmap');
      $ret = "$ret<a href='http://www.llmap.org/maps/by-code/$iso.html' target='_blank'><img style='width:36px;' class='favicon' src='img/extern/llmap.png' title='$tooltip'/></a>";
    }
    return $ret;
  }
  /**
    @param $t Translator
    @return $links String
    Combines the outputs of the functions
    get{MapsLink,WikipediaLink,VariousLinks}
    and adds the play image afterwards.
  */
  public function getLinks($t){
    return '<div id="languageInfo">'
      . $this->getMapsLink($t)
      . $this->getWikipediaLink($t)
      . $this->getVariousLinks($t)
      . '</div>';
  }
  /**
    @param $t Translator
    Displays the detailes information of a language.
  */
  public function getDescription($t){
    $sid = $this->getValueManager()->getStudy()->getId();
    $id = $this->id;
    $desc = '';
    $q = "SELECT "
       . "Tooltip"
       . ", SpecificLanguageVarietyName"
       . ", WebsiteSubgroupName "
       . ", WebsiteSubgroupWikipediaString "
       . ", HistoricalPeriod "
       . ", HistoricalPeriodWikipediaString "
       . ", StateRegion "
       . ", NearestCity "
       . ", PreciseLocality "
       . ", PreciseLocalityNationalSpelling "
       . ", ExternalWeblink "
       . "FROM Languages_$sid "
       . "WHERE LanguageIx = $id";
    if($r = $this->dbConnection->query($q)->fetch_assoc()){
      //Description lines:
      $description = $r['Tooltip'];
      if($description != '')
        $desc .= "<tr><td>$description</td></tr>";
      //Historical Period
      if($r['HistoricalPeriod'] != ''){
        $hPeriod = $r['HistoricalPeriod'];
        $wikiLink = '';
        if($r['HistoricalPeriodWikipediaString'] != '')
          $wikiLink = "<a href='http://en.wikipedia.org/wiki/".$r['HistoricalPeriodWikipediaString']."' target='_blank'><img src='http://en.wikipedia.org/favicon.ico' /></a>";
        $desc .= "<tr><td>".$t->st('language_description_historical').": $hPeriod$wikiLink</td></tr>";
      }
      //Region
      $nCity = $r['NearestCity'];
      $sRegion = $r['StateRegion'];
      if($nCity != ''){
        if($sRegion != '')
          $nCity = "$nCity ($sRegion)";
      }else{
        $nCity = $sRegion;
      }
      if($nCity != '')
        $desc .= "<tr><td>".$t->st('language_description_region').": $nCity</td></tr>";
      //Locality
      if($r['PreciseLocality'] != ''){
        $pLocality = $r['PreciseLocality'];
        $pNatSpelling = '';
        if($r['PreciseLocalityNationalSpelling'] != '')
          $pNatSpelling = ' (='.$r['PreciseLocalityNationalSpelling'].')';
        $desc .= "<tr><td>".$t->st('language_description_preciselocality').": $pLocality$pNatSpelling</td></tr>";
      }
      //External Weblink
      if($r['ExternalWeblink'] != ''){
        $link = $r['ExternalWeblink'];
        $desc .= "<tr><td>".$t->st('language_description_externalweblink').": <a href='$link' target='_blank'>$link</a></td></tr>";
      }
      //WebsiteSubgroup
      if($r['WebsiteSubgroupName'] != ''){
        $subgroupName = $r['WebsiteSubgroupName'];
        $wikiLink = '';
        if($r['WebsiteSubgroupWikipediaString'] != '')
          $wikiLink = '<a href="http://en.wikipedia.org/wiki/'.$r['WebsiteSubgroupWikipediaString'].'" target="_blank"><img src="http://en.wikipedia.org/favicon.ico" /></a>';
        $desc .= "<tr><td>".$t->st('language_description_subgroup').": $wikiLink$subgroupName</td></tr>";
      }
    }
    return $desc;
  }
  /**
    @return $familyIx FamilyIx from v4.Languages table
    Introduced because it determines the backgroundcolor
    of transcriptions on mapview.
    Defaults to 1
  */
  public function getFamilyIx(){
    if($r = $this->fetchFields('FamilyIx'))
      return $r[0];
    return 1;
  }
  /**
    @param $v ValueManager
    @param $next Bool false means prev
    @return $language Language
    This function works similar to database/Word:getNeighbour.
    It returns the next/prev Language from the current study as sorted by Id.
    Languages wrap around, meaning the last Language is the prev to the first.
    However, this function is easier than the Word implementation,
    because Languages are always sorted by their Id, and never alphabetically.
  */
  private function getNeighbour($v, $next){
    // Setting $order and $comp depending on $next:
    $order = $next ? 'ASC' : 'DESC';
    $comp  = $next ? '>'   : '<';
    //Setting Ids:
    $sId = $v->getStudy()->getId();
    $lId = $this->id;
    //The default Query:
    $q = "SELECT LanguageIx FROM RegionLanguages_$sId WHERE "
       . "(RegionGpIx, RegionMemberLgIx) $comp (SELECT RegionGpIx, RegionMemberLgIx FROM RegionLanguages_$sId WHERE LanguageIx = $lId) "
       . "ORDER BY RegionGpIx $order, RegionMemberLgIx $order LIMIT 1";
    if($r = $this->dbConnection->query($q)->fetch_row()){
      return new LanguageFromId($v, $r[0]);
    }
    //The wrap around case:
    $q = "SELECT LanguageIx FROM RegionLanguages_$sId ORDER BY RegionGpIx $order, RegionMemberLgIx $order LIMIT 1";
    $r = $this->dbConnection->query($q)->fetch_row();
    return new LanguageFromId($v, $r[0]);
  }
  /**
    @param $v ValueManager
    @return $next Language
    getNext acts as a proxy to getNeighbour.
  */
  public function getNext($v){
    return $this->getNeighbour($v, true);
  }
  /**
    @param $v ValueManager
    @return $prev Language
    getPrev acts as a proxy to getNeighbour.
  */
  public function getPrev($v){
    return $this->getNeighbour($v, false);
  }
  /**
    @return $phLang Language
    Returns the AssociatedPhoneticsLgForThisSpellingLg for a Language,
    if IsSpellingRfcLang = 1, or $this.
  */
  public function getPhoneticLanguage(){
    $r = $this->fetchFields('IsSpellingRfcLang','AssociatedPhoneticsLgForThisSpellingLg');
    if($r[0] == "1" && $r[1]){
      $id  = $this->id;
      $lid = $r[1];
      return new LanguageFromId($this->getValueManager(), $r[1]);
    }
    return $this;
  }
  /**
    @return [Contributor] contributors
  */
  public function getContributors(){
    return Contributor::forLanguage($this);
  }
  /***/
  public function hasTranscriptions(){
    $r = $this->fetchFields('IsOrthographyHasNoTranscriptions');
    if($r[0] == '1') return false;
    return true;
  }
}
/** Extends Language so that it can be created from an id. */
class LanguageFromId extends Language{
  /**
    @param $v ValueManager
    @param $id LanguageIx
  */
  public function __construct($v, $id){
    $this->setup($v);
    $this->id = $id;
    $this->findKey();
  }
}
/** Extends Language so that it can be created from a key. */
class LanguageFromKey extends Language{
  /**
    @param $v ValueManager
    @param $key String - shortname of the language.
  */
  public function __construct($v, $key){
    $this->setup($v);
    $this->key = $key;
    $this->id = null;
    $qs = array(
        "SELECT LanguageIx FROM RegionLanguages WHERE RegionGpMemberLgNameLongInThisSubFamilyWebsite LIKE '$key'"
      , "SELECT LanguageIx FROM RegionLanguages WHERE RegionGpMemberLgNameShortInThisSubFamilyWebsite LIKE '$key'"
      , "SELECT LanguageIx FROM Languages WHERE ShortName LIKE '$key'"
    );
    foreach($qs as $q){
      if($r = $this->fetchRow($q))
        $this->id = $r[0];
    }
    if($this->id == null)
      die("No Id found for LanguageKey: $key");
  }
}
/** Extends Language so that it can be created from a Study. */
class LanguageFromStudy extends Language{
  /**
    @param $v ValueManager
    @param $study Study
    Fetches the first Language in a study sorting by LanguageIx asc.
  */
  public function __construct($v, $study){
    $this->setup($v);
    $sid = $study->getId();
    //Fetching LanguageIx
    $q = "SELECT LanguageIx FROM Languages_$sid ORDER BY LanguageIx ASC LIMIT 1";
    if($r = $this->dbConnection->query($q)->fetch_row()){
      $this->id = $r[0];
    }else die("Could not find language for studyId: $sid.");
    $this->findKey();
  }
}
?>
