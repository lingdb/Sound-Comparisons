<?php
require_once 'DBEntry.php';
/**
  A Word is a DBEntry from one of the Words_$studyName tables.
*/
class Word extends DBEntry{
  protected $sid; //StudyId of the Study a Word belongs do.
  /**
    @param $field String The name of the field that is to be fetched.
    @return String The fields value for the Words entry in the Words table.
    Fetches a field with the given Name from the Words entry in the database.
  */
  protected function fetchField($field){
    $sid = $this->sid;
    $id = $this->id;
    $q = "SELECT $field FROM Words_$sid "
       . "WHERE CONCAT(IxElicitation, IxMorphologicalInstance) = $id";
    $row = Config::getConnection()->query($q)->fetch_row();
    return $row[0];
  }
  /**
    @return String
    To fetch the ModernName for a Word,
    we first lookup if the Word has already been translated
    into the language the site is displayed in.
    If that's not the case, this function falls back on fetching
    the FullRfcModernLg01 directly from the Table.
  */
  public function getModernName(){
    if($trans = $this->getValueManager()->getTranslator()->dt($this)){
      return $trans;
    }
    return $this->fetchField('FullRfcModernLg01');
  }
  /**
    @return String
    Fetches a Words ProtoName directly from the Table.
  */
  public function getProtoName(){
    return $this->fetchField('FullRfcProtoLg01');
  }
  /**
    @return IxElicitation String
    Returns the logical ordering of a Word.
    This logical ordering might for example be
    the ascending order of Words for Numbers.
    This way an ordering is possible independent
    of a language a Word may be translated into.
  */
  public function getIxElicitation(){
    return $this->fetchField('IxElicitation');
  }
  /**
    @return pathPart String
    The path to the soundfiles is composed of different parts,
    of which this metod returns one.
  */
  public function getPath(){
    return $this->fetchField('SoundFileWordIdentifierText');
  }
  /**
    @param $v ValueManager
    @param $useSpLang Bool
    @param $break Bool
    @return $translation String
    Returns the word translated for a given ValuaManager $v.
    If $useSpLang is true, the Word is translated via $v->gwo()->getSpLang().
    If $break is true, multiple translations of the same 'rank' will be imploded with '<br />',
    otherwise they will be imploded with ', '.
  */
  public function getTranslation($v, $useSpLang = false, $break = true){
    if(!$v) $v = $this->getValueManager();
    if($useSpLang) // The case to use the SpellingLanguage.
      $l = $v->gwo()->getSpLang();
    if(!isset($l)) // The case to use the ReferenceLanguage given by the Translator.
      $l = $v->getTranslator()->getRfcLanguage();
    if($l){ // Trying to translate.
      $sid = $this->sid;  // The id of the Study this word belongs in
      $id  = $this->id;   // The id of this Word
      $lId = $l->getId(); // The id of the Language we want to translate to
      // The query to fetch possible translations:
      $q = "SELECT SpellingAltv1, SpellingAltv2 FROM Transcriptions_$sid "
         . "WHERE LanguageIx = $lId "
         . "AND CONCAT(IxElicitation, IxMorphologicalInstance) = $id";
      $set = Config::getConnection()->query($q);
      $translations = array(); // Where translations are put in.
      // There might be multiple Transcriptions for a pair of (Word,Language),
      // therefore we iterate the resultset.
      while($r = $set->fetch_row()){
        //Examining the Pair:
        $str = $r[0];
        if($str === '')
          $str = $r[1];
        else if(strlen($r[1]) > 0)
          $str = $str.'/'.$r[1];
        if($str != '') // Pushing the result if we've got one
          array_push($translations, $str);
      }
      //Gluing translations together, removing duplicates:
      $glue = '<br/>';
      if(!$break) $glue = ', ';
      $ret = implode($glue, array_unique($translations));
      //Done:
      if($ret != '') return $ret;
    }
    /*Fallback on fail*/
    return $this->getModernName();
  }
  /**
    @param $v ValueManager
    @param $next Bool false means prev
    @return [$word] Word
    This function is designed to be called by one getNext or getPrev
    which act as proxies to it.
    It fetches a neighbour to a Word either by using IxElicitation
    or by RfcLanguage, depending on the ValueManager.
    Neighbours are viewed as cyclic, this means that the last Word
    in any order is a Neighbour to the first and vice versa.
  */
  private function getNeighbour($v, $next){
    // Setting $order and $comp depending on $next:
    $order = $next ? 'ASC' : 'DESC';
    $comp  = $next ? '>'   : '<';
    // Building the query:
    $sId = $v->getStudy()->getId();
    if($v->gwo()->isLogical()){ // Fetching neighbours by logical order
      $eli   = $this->getIxElicitation();
      $query = "SELECT CONCAT(IxElicitation, IxMorphologicalInstance) FROM Words_$sId "
             . "WHERE MeaningGroupIx != 0 "
             . "AND IxElicitation != $eli "
             . "AND MeaningGroupIx $comp= (SELECT DISTINCT MeaningGroupIx FROM Words_$sId WHERE IxElicitation = $eli LIMIT 1) "
             . "AND MeaningGroupMemberIx $comp= (SELECT DISTINCT MeaningGroupMemberIx FROM Words_$sId WHERE IxElicitation = $eli LIMIT 1) "
             . "ORDER BY MeaningGroupIx $order, MeaningGroupMemberIx $order, IxElicitation $order LIMIT 1";
    }else if($v->gwo()->isAlphabetical()){
      $id = $this->id;
      if($spL  = $v->gwo()->getSpLang()){ // Fetching neighbours by alphabetical order in the current translation.
        $rfcId = $spL->getId();
        $q     = "SELECT CONCAT(T.SpellingAltv1, T.SpellingAltv2, W.FullRfcModernLg01) "
               . "FROM Words_$sId AS W JOIN Transcriptions_$sId AS T USING (IxElicitation, IxMorphologicalInstance) "
               . "WHERE T.LanguageIx = $rfcId "
               . "AND CONCAT(W.IxElicitation, W.IxMorphologicalInstance) = $id "
               . "LIMIT 1";
        $trans = Config::getConnection()->query($q)->fetch_row();
        $trans = $trans[0];
        $query = "SELECT CONCAT(W.IxElicitation, W.IxMorphologicalInstance) "
               . "FROM Words_$sId AS W JOIN Transcriptions_$sId AS T USING (IxElicitation, IxMorphologicalInstance) "
               . "WHERE T.LanguageIx = $rfcId "                                   // Is in current language
               . "AND CONCAT(W.IxElicitation, W.IxMorphologicalInstance) != $id " // Is not current word
               . "AND CONCAT(T.SpellingAltv1, T.SpellingAltv2, W.FullRfcModernLg01) $comp '$trans' "      // Obeys current sorting
               . "ORDER BY CONCAT(T.SpellingAltv1, T.SpellingAltv2, W.FullRfcModernLg01) $order LIMIT 1"; // Comes in correct order
      }else{ // Alphabetical neighbours in the current translation without spLang.
        $tid   = $v->gtm()->getTarget();
        $q     = "SELECT Trans_FullRfcModernLg01 "
               . "FROM Page_DynamicTranslation_Words "
               . "WHERE TranslationId = $tid "
               . "AND CONCAT(IxElicitation, IxMorphologicalInstance) = $id";
        $trans = Config::getConnection()->query($q)->fetch_row();
        $trans = $trans[0];
        $query = "SELECT CONCAT(IxElicitation, IxMorphologicalInstance) "
               . "FROM Page_DynamicTranslation_Words "
               . "WHERE TranslationId = $tid "
               . "AND CONCAT(IxElicitation, IxMorphologicalInstance) != $id "
               . "AND Trans_FullRfcModernLg01 < '$trans' "
               . "AND CONCAT(IxElicitation, IxMorphologicalInstance) = ANY("
                 . "SELECT CONCAT(IxElicitation, IxMorphologicalInstance) "
                 . "FROM Words_$sId"
               .") ORDER BY Trans_FullRfcModernLg01 ASC LIMIT 1;";
      }
    }
    //Trying to fetch the Word:
    if($w = Config::getConnection()->query($query)->fetch_row()){
      return new WordFromId($this->v, $w[0]);
    }
    //No Word found, so this is the loop around case:
    if($v->gwo()->isLogical()){
      if($next){ //Fetch first word
        $query = "SELECT CONCAT(IxElicitation, IxMorphologicalInstance) "
          . "FROM Words_$sId WHERE MeaningGroupIx != 0 "
          . "ORDER BY MeaningGroupIx ASC, MeaningGroupMemberIx ASC, IxElicitation ASC LIMIT 1";
      }else{ //Fetch last word
        $query = "SELECT CONCAT(IxElicitation, IxMorphologicalInstance) "
          . "FROM Words_$sId WHERE MeaningGroupIx != 0 "
          . "ORDER BY MeaningGroupIx DESC, MeaningGroupMemberIx DESC, IxElicitation DESC "
          . "LIMIT 1";
      }
    }else if($v->gwo()->isAlphabetical()){
      if($spL = $v->gwo()->getSpLang()){
        $rfcId = $spL->getId();
        if($next){//Fetch first word
          $query = "SELECT CONCAT(W.IxElicitation, W.IxMorphologicalInstance) "
               . "FROM Words_$sId AS W "
               . "JOIN Transcriptions_$sId AS T USING (IxElicitation, IxMorphologicalInstance) "
               . "WHERE T.LanguageIx = 11111110102 "
               . "ORDER BY CONCAT(T.SpellingAltv1, T.SpellingAltv2, W.FullRfcModernLg01) ASC LIMIT 1";
        }else{//Fetch last word
          $query = "SELECT CONCAT(W.IxElicitation, W.IxMorphologicalInstance) "
               . "FROM Words_$sId AS W JOIN Transcriptions_$sId AS T USING (IxElicitation, IxMorphologicalInstance) "
               . "WHERE T.LanguageIx = $rfcId "
               . "ORDER BY CONCAT(T.SpellingAltv1, T.SpellingAltv2, W.FullRfcModernLg01) DESC LIMIT 1";
        }
      }else{ //Alphabetical first/last in current translation without spLang.
        $tid = $v->gtm()->getTarget();
        $order = $next ? 'ASC' : 'DESC';
        $query = "SELECT CONCAT(IxElicitation, IxMorphologicalInstance) "
               . "FROM Page_DynamicTranslation_Words "
               . "WHERE TranslationId = $tid "
               . "AND CONCAT(IxElicitation, IxMorphologicalInstance) = ANY ("
                 . "SELECT CONCAT(IxElicitation, IxMorphologicalInstance) "
                 . "FROM Words_$sId"
               .") ORDER BY Trans_FullRfcModernLg01 $order LIMIT 1";
      }
    }
    if($w = Config::getConnection()->query($query)->fetch_row())
      return new WordFromId($this->v, $w[0]);
    //This should no more occur:
    return null;
  }
  /**
    @param $v ValueManager
    @return $next Word
    getNext tries to find the next Neighbour to a Word depending on a ValueManager.
  */
  public function getNext($v){
    return $this->getNeighbour($v, true);
  }
  /**
    @param $v ValueManager
    @return $prev Word
    getPrev tries to find the previous Neighbout to a Word depending on a ValueManager.
  */
  public function getPrev($v){
    return $this->getNeighbour($v, false);
  }
  /**
    @return $mgs MeaningGroup[]
    Returns all the MeaningGroups that a Word belongs to.
  */
  public function getMeaningGroups(){
    $v   = $this->getValueManager();
    $id  = $this->getId();
    $sid = $v->getStudy()->getId();
    $queries = array(
      "SELECT DISTINCT MeaningGroupIx FROM MeaningGroupMembers "
    . "WHERE CONCAT(IxElicitation, IxMorphologicalInstance) = $id "
    . "AND CONCAT(StudyIx, FamilyIx) LIKE ("
    . "SELECT CONCAT(StudyIx, REPLACE(FamilyIx, 0, '%')) FROM Studies WHERE Name = '$sid')"
    , "SELECT DISTINCT MeaningGroupIx FROM MeaningGroupMembers "
    . "WHERE CONCAT(IxElicitation, IxMorphologicalInstance) = $id"
    );
    foreach($queries as $q){
      $set = Config::getConnection()->query($q);
      $mgs = array();
      while($r = $set->fetch_row())
        array_push($mgs, new MeaningGroupFromId($v, $r[0]));
      if(count($mgs) > 0)
        return $mgs;
    }
    Config::error("Could not find MeaningGroups in Word:getMeaningGroups() for id:$id in study:$sid.");
  }
  /**
    @param $w Word
    @return $share Bool
    Checks, if the given Word shares at least
    one MeaningGroup with this Word.
  */
  public function sameMeaningGroup($w){
    if($w == null) return false;
    $mIds = array();
    foreach($this->getMeaningGroups() as $m)
      array_push($mIds, $m->getId());
    foreach($w->getMeaningGroups() as $m)
      if(in_array($m->getId(), $mIds))
        return true;
    return false;
  }
  /**
    @param $t Translator
    @return $a String A html link
    Returns a link to view the Word in MapView,
    depending on the current Translation.
  */
  public function getMapsLink($t){
    $tooltip = $t->st("tooltip_words_link_mapview");
    $v = $this->getValueManager();
    $href = $v->gpv()->setView('MapView')->setWord($this)->link();
    return "<a $href><img class='favicon' src='img/maps.png' title='$tooltip' /></a>";
  }
  /***/
  public static function compareOnTranslation($x, $y){
    $v  = $x->getValueManager();
    $tx = $x->getTranslation($v, true);
    $ty = $y->getTranslation($v, true);
    if($tx === $ty) return 0;
    return ($tx > $ty) ? 1 : -1;
  }
  /**
    @param Word[] words
    @return {mgs: MgId -> MeaningGroup, buckets: MgId -> Word[]}
    This method is implemented after the preimage
    of Language::mkRegionBuckets.
  */
  public static function mkMGBuckets($words){
    $mgs     = array(); // MgId -> MeaningGroup
    $buckets = array(); // MgId -> Word[]
    //Sorting into buckets:
    foreach($words as $w){
      foreach($w->getMeaningGroups() as $m){
        $mId = $m->getId();
        if(!array_key_exists($mId, $mgs)){
          $mgs[$mId]     = $m;
          $buckets[$mId] = array($w);
        }else{
          array_push($buckets[$mId], $w);
        }
      }
    }
    //Enumerating words in buckets:
    $i = 0;
    foreach($buckets as $mId => $bucket){
      $newBucket = array();
      foreach($bucket as $w){
        $newBucket[$i] = $w;
        $i++;
      }
      $buckets[$mId] = $newBucket;
    }
    //Done:
    return array('mgs' => $mgs, 'buckets' => $buckets);
  }
}
/**
  WordFromKey extends a Word to provide a constructor
  that can create a Word given it's key, a ValueManager
  and optional a StudyId.
*/
class WordFromKey extends Word{
  /**
    @param $v ValueManager
    @param $key String Word Name
    @param [$sid] String Studies.Name
    Tries to create a Word from the given parameters,
    or dies if the Word cannot be found.
  */
  public function __construct($v, $key, $sid = null){
    $this->setup($v);
    $this->key = $key;
    if($sid === null){
      $sid = $v->getStudy()->getId();
    }
    if($sid){
      $q = "SELECT CONCAT(IxElicitation, IxMorphologicalInstance) FROM Words_$sid "
       . "WHERE (FullRfcModernLg01 LIKE '$key' OR FullRfcProtoLg01 LIKE '$key')";
    }else Config::error('main/database/Word.php:WordFromKey has no $studyId given!');
    if($word = Config::getConnection()->query($q)->fetch_row()){
      $this->id = $word[0];
      $this->sid = $sid;
    }else Config::error("Could not find word: $key");
  }
}
/** WordFromId provides a constructor to create a Word from it's id. */
class WordFromId extends Word{
  /**
    @param $v ValueManager
    @param $id Int
    @param [$study] Study
  */
  public function __construct($v, $id, $study = null){
    $this->setup($v);
    $this->id = $id;
    if($study){
      $sid = $study->getId();
    }else if($study = $this->getValueManager()->getStudy()){
      $sid = $study->getId();
    }else Config::error('Could not find $sid in main/database/Word.php:WordFromId:__construct() with id:\t'.$id);
    $query = "SELECT FullRfcModernLg01 FROM Words_$sid WHERE CONCAT(IxElicitation, IxMorphologicalInstance) = $id";
    if($word = Config::getConnection()->query($query)->fetch_assoc()){
      $this->key = $word['FullRfcModernLg01'];
      $this->sid = $sid;
    }else Config::error("Invalid WordId: '$id' with query: $query");
  }
}
/**
  WordFromStudy provides a constructor to create the Word with the lowest
  IxElicitation inside the given Study.
*/
class WordFromStudy extends Word{
  /**
    @param $v ValueManager
    @param $study Study
    Tries to fetch the word with the lowest IxElicitation that belongs inside the given study.
  */
  public function __construct($v, $study){
    $this->setup($v);
    $sid = $study->getId();
    $q = "SELECT CONCAT(W.IxElicitation, W.IxMorphologicalInstance), W.FullRfcModernLg01 "
       . "FROM Words_$sid ORDER BY IxElicitation ASC LIMIT 1";
    if($r = Config::getConnection()->query($q)->fetch_row()){
      $this->id  = $r[0];
      $this->key = $r[1];
      $this->sid = $sid;
    }else Config::error("No Word found for StudyId: $sid");
  }
}
?>
