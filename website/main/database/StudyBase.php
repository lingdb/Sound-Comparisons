<?php
require_once 'Translatable.php';
require_once 'MeaningGroups.php';
/**
  The StudyBase is extended by the Study class.
  It's purpose is to weed out some more basic functions,
  and to introduce caching for them.
*/
class StudyBase extends Translatable{
  //Inherited from Translatable:
  protected static function getTranslationPrefix(){
    return 'StudyTitleTranslationProvider';
  }
  /**
    Inherited from Translatable:
    The only thing translated so far is the title, and thus we don't need columns.
    Required option fields:
    tId, [id]
  */
  public static function getTranslation($options){
    $field = array_key_exists('id', $options)
           ? $options['id']
           : RedirectingValueManager::getInstance()->gsm()->getStudy()->getId();
    $category = StudyBase::getTranslationPrefix();
    return Translatable::getTrans($options['tId'], $category, $field);
  }
  /**
    Inherited from Translatable:
    Required option fields:
    tId
  */
  public function translate($options){
    $options['id'] = $this->id;
    return $this::getTranslation($options);
  }
  /**
    Since getWords is usually called at least once per site,
    it makes sense to cache all Word objects,
    instead of recreating them each time.
  */
  private $words = null;
  /** This method is overwritten by Study. */
  public function getWords(){
    Stopwatch::start('StudyBase:getWords');
    if(is_null($this->words)){
      $id = $this->id;
      $q = "SELECT CONCAT(IxElicitation, IxMorphologicalInstance) FROM Words_$id";
      $set = Config::getConnection()->query($q);
      $words = array();
      while($r = $set->fetch_row()){
        array_push($words, new WordFromId($this->v, $r[0]));
      }
      $this->words = $words;
    }
    Stopwatch::stop('StudyBase:getWords');
    return $this->words;
  }

}
?>
