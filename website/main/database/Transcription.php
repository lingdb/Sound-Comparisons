<?php
require_once 'DBTable.php';
/**
  The Transcription is a link between a Word and a Language.
  There may be several entries in the Transcriptions Table
  for a given Pair (Word,Language), and an instance of Transcription
  encapsules all these pairs.
*/
class Transcription extends DBTable{
  protected $word     = null; // The Word the Transcription belongs to
  protected $language = null; // The Language the Transcription belongs to
  protected $sid      = null; // The id of the Study the Transcription belongs in
  /***/
  protected function buildSelectQuery($fs){
    $wid = $this->word->getId();
    $lid = $this->language->getId();
    $sid = $this->sid;
    return "SELECT $fs "
         . "FROM Transcriptions_$sid "
         . "WHERE LanguageIx = $lid "
         . "AND CONCAT(IxElicitation, IxMorphologicalInstance) = $wid";
  }
  /**
    @param $v ValueManager
    @return $path String
    Returns the path to the soundfile without it's file suffix.
    This path is composed of the path parts from the Language
    and the Word aswell as a static bit comming from the Config class.
  */
  public function getSoundFilePath($v){
    $soundPath = Config::$soundPath;
    $langPath  = $this->language->getPath();
    $wordPath  = $this->word->getPath();
    return "$soundPath/$langPath/$langPath$wordPath";
  }
  /**
    @param $extensions String[] file extensions to look for
    @return $files String[] complete locations of existing soundfiles
  */
  public function getSoundFiles($extensions = array('.mp3','.ogg')){
    $files = array();
    //Alternative realisation:
    $path = $this->getSoundFilePath($this->word->getValueManager());
    foreach($this->fetchFieldRows('AlternativePhoneticRealisationIx', 'AlternativeLexemIx') as $alt){
      $pro = ($alt[0] > 1) ? '_pron'.$alt[0] : '';
      $lex = ($alt[1] > 1) ? '_lex'.$alt[1]  : '';
      $found = array();
      foreach($extensions as $ext){
        $f = $path.$lex.$pro.$ext; // The complete path to the file
        if(file_exists($f))
          array_push($found, $f);
      }
      array_push($files, $found);
    }
    return $files;
  }
  /***/
  public static function soundFileIsPron($file){
    return preg_match('/_pron/', $file);
  }
  /***/
  public static function soundFileIsLex($file){
    return preg_match('/_lex/', $file);
  }
  /**
    @param [$default = ''] String value that replaces empty values
    @return $ret String[]
  */
  public function getTranscriptions($default = ''){
    $ret = array();
    foreach($this->fetchFieldRows('Phonetic') as $r){
      $t = ($r[0] === '') ? $default : $r[0];
      array_push($ret, $t);
    }
    return $ret;
  }
  /**
    @return [(String,String)]
  */
  public function getSuperscriptInfo(){
    $ret = array();
    $rows = $this->fetchFieldRows('NotCognateWithMainWordInThisFamily'
                                 ,'CommonRootMorphemeStructDifferent');
    foreach($rows as $r){
      if($r[0] == '1'){
        array_push($ret, array('NC!','tooltip_transcription_notcognate'));
      }else if($r[1] == '1'){
        array_push($ret, array('DM','tooltip_transcription_differentmorpheme'));
      }else{
        array_push($ret, null);
      }
    }
    return $ret;
  }
  /**
    @return $ret Bool[]
  */
  public function getNotCognates(){
    $ret = array();
    foreach($this->fetchFieldRows('NotCognateWithMainWordInThisFamily') as $r){
      array_push($ret, ($r[0] == '1'));
    }
    return $ret;
  }
  /**
    @param [$v] ValueManager
    @return [Html] phonetics
    Returns an array of divs.
  */
  public function getPhonetics($v = null){
    if(!$v) $v = $this->word->getValueManager();
    $t = $v->getTranslator();
    $phonetics = $this->getTranscriptions('PLAY');
    $sources   = $this->getSoundFiles($v->getSoundFiles());
    $supInfo   = $this->getSuperscriptInfo();
    $ret       = array();
    foreach($phonetics as $i => $phonetic){
      //Historical:
      $h = '';
      if($this->getLanguage()->isHistorical())
        $h = '*';
      //Not cognate:
      $sInf = '';
      if($s = $supInfo[$i]){
        $sInf    = $s[0];
        $tooltip = $t->st($s[1]);
        $sInf    = "<div class='superscript' title='$tooltip'>$sInf</div>";
      }
      //Source files:
      $srcs = $sources[$i];
      //File missing:
      $fm = '';
      if(count($srcs) == 0){
        $fm = ' fileMissing';
        if($phonetic === 'PLAY'){
          $phonetic = '--';
          $success  = false;
        }
      }
      //We like JSON:
      $srcs = json_encode($srcs);
      //Complete:
      $subscript = '';
      if(count($phonetics) > 1 && Transcription::soundFileIsLex($srcs)){
        $ttip = $t->st('tooltip_subscript_differentVariants');
        $subscript = '<div class="subscript" title="'.$ttip.'">'.($i+1).'</div>';
      }
      $phonetic = "<div class='transcription$fm'>$phonetic</div>";
      if($this->language->hasTranscriptions()){
        $phonetic = "[$phonetic]";
      }else $phonetic = "|$phonetic|";
      $audio = "<audio data-onDemand='$srcs' preload='auto' autobuffer=''></audio>";
      array_push($ret, "<div class='audio'>$h$phonetic$subscript$sInf$audio</div>");
    }
    return $ret;
  }
  /**
    @param [$v] ValueManager
    @param [$break = false] Bool
    @param [&$success = true] Bool
    @return $phonetic String
    Returns a div containing everything needed to play the audio aswell as the phonetic transcription.
    If the break parameter is true, and more than one phonetic is generated, phonetics will be seperated by <br />.
    $success will be false if neither a soundfile nor phonetics are available.
  */
  public function getPhonetic($v = null, $break = false, &$success = true){
    $ps = $this->getPhonetics($v);
    $success = (count($ps) == 0);
    $glue = $break ? '<br>' : '';
    return implode($glue, $ps);
  }
  /**
    @return $word Word
    Returns the Word that belongs to the Transcription.
  */
  public function getWord(){
    return $this->word;
  }
  /**
    @return $language Language
    Returns the Language that belongs to the Transcription.
  */
  public function getLanguage(){
    return $this->language;
  }
  /**
    @param [$v ValueManager]
    @return [$altSpelling] String
    Returns the alternative Spelling of a Word, which is attached to the Transcription.
    If an altSpelling can't be found, but the Transcriptions Language is an RfcLanguage,
    the ModernName of the Word is returned.
  */
  public function getAltSpelling($v = null){
    if($alts = $this->fetchFields('SpellingAltv1','SpellingAltv2')){
      $s1 = $alts[0];
      $s2 = $alts[1];
      //If the language-name Starts with 'Proto-',
      //we should put a * in front of the altSpelling.
      $proto = '';
      if(preg_match('/^Proto-/', $this->language->getShortName()))
        $proto = '*';
      //Returning
      if(strlen($s2) > 0) $altSpelling = $proto.$s2;
      if(strlen($s1) > 0) $altSpelling = $proto.$s1;
      if(isset($altSpelling)){
        $wTrans = $this->word->getTranslation($v, true, false);
        if($altSpelling != $wTrans) return $altSpelling;
      }
      //If the Language is a RfcLanguage, we may return it's ModernName
      if($this->language->isRfcLanguage()){
        return $this->word->getModernName();
      }
      //If the Language has a RfcLanauge, we use that:
      if($l = $this->language->getRfcLanguage()){
        $t = new TranscriptionFromWordLang($this->word, $l);
        return $t->getAltSpelling($v);
      }
    }
    return null;
  }
  /**
    @return json Array - Data ready to be used with json_encode
  */
  public function toJSON(){
    $v = $this->word->getValueManager();
    //Translation:
    $translation = $this->word->getTranslation($v);
    //Language:
    $langName = $this->language->getShortName(false);
    $langName = '"' . $langName . '"';
    //FamilyIx via Language:
    $familyIx = $this->language->getFamilyIx();
    //Location:
    $latlon = $this->language->getLocation();
    $lat    = $latlon[0];
    $lon    = $latlon[1];
    //Historical:
    $historical = 0;
    if($this->language->isHistorical())
      $historical = 1;
    //Altspellings:
    $alt = $this->getAltSpelling($v);
    if(!$alt) $alt = '';
    //Phonetics and Soundfiles:
    $hasSoundFile = false;
    $path = $this->getSoundFilePath($v);
    $psf  = array(); //An Array of tuples of soundfiles and phonetics.
    foreach($this->fetchFieldRows('Phonetic', 'AlternativePhoneticRealisationIx', 'AlternativeLexemIx') as $r){
      $p   = ($r[0] === '') ? 'PLAY' : $r[0];
      $pro = ($r[1] > 1) ? '_pron'.$r[1] : '';
      $lex = ($r[2] > 1) ? '_lex'.$r[2]  : '';
      $sf  = array(); // A list of soundfiles.
      foreach($v->getSoundFiles() as $ext){
        $file = $path.$lex.$pro.$ext;
        if(file_exists($file)){
          array_push($sf, $file);
          $hasSoundFile = true;
        }
      }
      if(!$hasSoundFile && $p === 'PLAY')
        $p = '--';
      $tuple = array('phonetic' => $p, 'soundfiles' => $sf);
      array_push($psf, $tuple);
    }
    //Color:
    $color = array(
      'color'      => '#'.$this->language->getRegion()->getColor()
    , 'opacity'    => $this->language->getOpacity()
    , 'colorDepth' => $this->language->getColorDepth()
    );
    //Languagelink:
    $languageLink = $v->gpv()->setView('LanguageView')->setLanguage($this->language)->setWords()->link();
    $languageLink = preg_replace('/"/', '\\"', $languageLink);
    $languageLink = preg_replace("/'/", "\\'", $languageLink);
    //Complete JSON:
    $data = array(
      'altSpelling'        => $alt
    , 'translation'        => $translation
    , 'lat'                => $lat
    , 'lon'                => $lon
    , 'historical'         => $historical
    , 'phoneticSoundfiles' => $psf
    , 'langName'           => $langName
    , 'languageLink'       => $languageLink
    , 'familyIx'           => $familyIx
    , 'color'              => $color
    );
    return $data;
  }
}
/**
  TranscriptionsFromWordLang adds a constructor to Transcription
  that allows to create a Transcription for a given pair of Word and Language.
*/
class TranscriptionFromWordLang extends Transcription{
  /**
    @param $word Word
    @param $language Language
  */
  public function __construct($word, $language){
    if(!isset($word))
      Config::error('Invalid Word in TranscriptionFromWordLang');
    if(!isset($language))
      Config::error('Invalid Language in TranscriptionFromWordLang');
    $this->word     = $word;
    $this->language = $language;
    $this->sid      = $language->getStudy()->getId();
  }
}
?>
