<?php
require_once 'SubManager.php';

abstract class WordManager extends SubManager{
  protected $words  = array();
  //Words need to be sorted, on the first call to getWords.
  protected $sorted = false;
  /**
    @return $words Word[]
  */
  public function getWords(){
    if(!$this->sorted){
      if($this->getValueManager()->gwom()->isAlphabetical()){
        usort($this->words, 'Word::compareOnTranslation');
      }else{
        usort($this->words, 'DBEntry::compareOnId');
      }
      $this->sorted = true;
    }
    return $this->words;
  }
  /**
    @param $words Word[]
    @return $v ValueManager
  */
  public function setWords($words = null){
    if(!isset($words))
      $words = array();
    $w = clone $this;
    $w->words = $words;
    return $this->gvm()->setM($w);
  }
  /**
    @param $word Word
    @return $v ValueManager
  */
  public function setWord($word){
    return $this->setWords(array($word));
  }
  /**
    Sets the words without referential transparency.
    @param $words Word[]
  */
  public function unsafeSetWords($words){
    $this->words = $words;
  }
  /**
    @param $word Word|Word[]
    @return $v ValueManager
  */
  public function addWord($word){
    if(!is_array($word))
      $word = array($word);
    return $this->setWords(array_merge($this->words, $word));
  }
  /**
    Pageview dependent adding of a word.
    @param $word Word
    @return $v ValueManager
  */
  public function pvAddWord($word){
    $pv = $this->gvm()->gpv();
    if($pv->isSelection())
      return $this->addWord($word);
    if($pv->isMapView())
      return $this->setWord($word);
    return $this->gvm()
                ->setRegions()
                ->setLanguages()
                ->gpv()->setSingleView()
                ->setWord($word);
  }
  /**
   * If no $word is given hasWord checks if the ValueManager holds any words at all.
   * @param [word] Word
   * @return has Bool
   * */
  public function hasWord($word = null){
    if($word == null)
      return (count($this->words) > 0);
    foreach($this->words as $w)
      if($w->getId() == $word->getId())
        return true;
    return false;
  }
  /**
    @param $words Word[]
    @return has String
    Tells, if 'all', 'none' or 'some' of the words are hold.
  */
  public function hasWords($words){
    return DBEntry::tellIntersection($this->words, $words);
  }
  /**
    @param $ws Word|Word[]
    @return $v ValueManager
  */
  public function delWord($ws){
    if(!is_array($ws))  // Chk if arg is array.
      return $this->delWord(array($ws));
    $ws = DBEntry::difference($this->words, $ws);
    return $this->setWords($ws);
  }
  /** Overwrites SubManager:pack() */
  public function pack(){
    if(count($this->words) <= 0)
      return array();
    $wNames = array();
    foreach($this->words as $w)
      array_push($wNames, $this->encodeUrl($w->getKey()));
    return array('words' => implode(',',$wNames));;
  }
  /***/
  public function getDefaults($multiple = false){
    $s = $this->gvm()->getStudy();
    if($multiple)
      return $s->getDefaultWords();
    return array($s->getDefaultWord());
  }
}

class InitWordManager extends WordManager{
  /**
    @param $v ValueManager
  */
  public function __construct($v){
    $this->setValueManager($v);
    if(isset($_GET['words'])){
      $studyId = $this->gvm()->gsm()->getStudy()->getId();
      foreach(array_unique(explode(',',$_GET['words'])) as $key){
        $key = $this->decodeUrl($key);
        $w   = new WordFromKey($this->gvm(), $key, $studyId);
        array_push($this->words, $w);
      }
    }
  }
  /***/
  public function getName(){return "WordManager";}
}
?>
