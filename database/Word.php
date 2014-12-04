<?php
require_once 'Translatable.php';
/**
  A Word is a DBEntry from one of the Words_$studyName tables.
*/
class Word extends Translatable{
  //Inherited from Translatable:
  protected static function getTranslationPrefix(){
    return 'WordsTranslationProvider-Words_-Trans_';
  }
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
    Some Words have a LongName in addition to their ModernNames.
  */
  public function getLongName(){
    if($trans = $this->getValueManager()->getTranslator()->getWordLongTranslation($this)){
      return $trans;
    }
    $f = $this->fetchField('LongerRfcModernLg01');
    return ($f === '') ? null : $f;
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
  public function getWordTranslation($v, $useSpLang = false, $break = true){
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
      $glue = $break ? '<br/>' : ', ';
      $ret = implode($glue, array_unique($translations));
      //Done:
      if($ret != ''){
        return $ret;
      }
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
    $key = null;
    $words = $v->getStudy()->getWords();
    foreach($words as $k => $w){
      if($w->getId() === $this->id){
        $key = $k;
        break;
      }
    }
    $key += $next ? 1 : -1;
    $key %= count($words);
    if($key < 0){ // Making sure modulo wraps
      $key += count($words);
    }
    if(array_key_exists($key, $words)){
      return $words[$key];
    }
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
    $q = "SELECT StudyIx, FamilyIx FROM Studies WHERE Name = '$sid'";
    $r = Config::getConnection()->query($q)->fetch_row();
    $sIx = $r[0]; $fIx = $r[1];
    $q = "SELECT DISTINCT MeaningGroupIX "
       . "FROM MeaningGroupMembers "
       . "WHERE StudyIx = $sIx "
       . "AND (FamilyIx = 0 OR FamilyIx = $fIx) "
       . "AND CONCAT(IxElicitation, IxMorphologicalInstance) = $id";
    $set = Config::getConnection()->query($q);
    $mgs = array();
    while($r = $set->fetch_row())
      array_push($mgs, new MeaningGroupFromId($v, $r[0]));
    if(count($mgs) > 0){
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
    return array(
      'link' => $this->getValueManager()->gpv()->setView('MapView')->setWord($this)->link()
    , 'ttip' => $t->st("tooltip_words_link_mapview")
    );
  }
  /**
    @param $x Word
    @param $y Word
    Compares two Words by their translation.
    If the php5-intl package is installed, it uses the Collator given by the config,
    which is the better way to do this.
    Otherwise, this function falls back on mysql to sort UTF8 strings,
    which is quite slower.
    With php5-intl:
    Times (min, avg, max): 1.5812, 3.5744550561798, 4.8371
    Without php5-intl:
    Times (min, avg, max): 2.1598, 4.0123397727273, 16.2653
    - The 16s spike might actually be an export link,
      so I thikn the min values are the best to look at.
      Also this data will change over time, and is just a hint.
  */
  public static function compareOnTranslation($x, $y){
    $v  = $x->getValueManager();
    $tx = $x->getWordTranslation($v, true);
    $ty = $y->getWordTranslation($v, true);
    //Checking if we've got php5-intl:
    if($c = Config::getCollator()){
      return $c->compare($tx,$ty);
    }
    //Fallback on mysql:
    $db = Config::getConnection();
    $tx = $db->escape_string($tx);
    $ty = $db->escape_string($ty);
    $q  = "SELECT '$tx' = '$ty', '$tx' > '$ty'";
    $r  = $db->query($q)->fetch_row();
    if($r[0] == 1) return 0;
    return ($r[1] == 1) ? 1 : -1;
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
