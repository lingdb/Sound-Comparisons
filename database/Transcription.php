<?php
require_once 'DBTable.php';
require_once 'Superscript.php';
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
    if(is_array($file)){
      foreach($file as $f)
        if(Transcription::soundFileIsLex($f))
          return true;
      return false;
    }
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
    $ret  = array();
    $rows = $this->fetchFieldRows(
      'NotCognateWithMainWordInThisFamily'
    , 'CommonRootMorphemeStructDifferent'
    , 'DifferentMeaningToUsualForCognate'
    , 'ActualMeaningInThisLanguage'
    , 'OtherLexemeInLanguageForMeaning'
    , 'RootIsLoanWordFromKnownDonor'
    , 'RootSharedInAnotherFamily'
    , 'IsoCodeKnownDonor'
    );
    foreach($rows as $r){
      if($r[0] === '1')
        array_push($ret, Superscript::forTranscription('NotCognateWithMainWordInThisFamily'));
      if($r[1] === '1')
        array_push($ret, Superscript::forTranscription('CommonRootMorphemeStructDifferent'));
      if($r[2] === '1')
        array_push($ret, Superscript::forTranscription('DifferentMeaningToUsualForCognate'));
      if(!empty($r[3])){
        $s = Superscript::forTranscription('ActualMeaningInThisLanguage');
        $s[1] .= $r[3];
        array_push($ret, $s);
      }
      if(!empty($r[4])){
        $s = Superscript::forTranscription('OtherLexemeInLanguageForMeaning');
        $s[1] .= $r[4];
        array_push($ret, $s);
      }
      if($r[5] === '1')
        array_push($ret, Superscript::forTranscription('RootIsLoanWordFromKnownDonor'));
      if($r[6] === '1')
        array_push($ret, Superscript::forTranscription('RootSharedInAnotherFamily'));
      if(!empty($r[7])){
        array_push($ret, Superscript::forTranscription($r[7]));
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
      //Source files:
      $srcs = $sources[$i];
      $_srcs = array();
      foreach($srcs as $s){
        $_s = preg_replace('/ogg$/','mp3', $s);
        array_push($_srcs, substr($_s, strlen(Config::$soundPath)));
      }
      //Current phonetic:
      $p = array(
        'historical'  => $this->getLanguage()->isHistorical()
      , 'fileMissing' => count($srcs) === 0
      , 'phonetic'    => ($phonetic === 'PLAY') ? '--' : $phonetic
      , 'srcs'        => json_encode($srcs)
      , '_srcs'       => $_srcs
      , 'hasTrans'    => $this->language->hasTranscriptions()
      , 'identifier'  => array(
          'word'      => $this->word->getId()
        , 'language'  => $this->language->getId()
        , 'study'     => $this->sid
        , 'n'         => $i
        )
      );
      //Not cognate:
      if(array_key_exists($i, $supInfo)){
        if($s = $supInfo[$i]){
          $p['notCognate'] = array(
            'sInf' => $s[0]
          , 'ttip' => $s[1]
          );
        }
      }
      //Subscript:
      if(count($phonetics) > 1 && Transcription::soundFileIsLex($srcs)){
        $p['subscript'] = array(
          'ttip'      => $t->st('tooltip_subscript_differentVariants')
        , 'subscript' => $i + 1
        );
      }
      //Done:
      array_push($ret, $p);
    }
    return $ret;
  }
  /**
    @param [$v] ValueManager
    @param [$break = false] Bool
    @param [$success = true] Bool
    @return $phonetic String
    Returns a div containing everything needed to play the audio aswell as the phonetic transcription.
    If the break parameter is true, and more than one phonetic is generated, phonetics will be seperated by <br />.
    $success will be false if neither a soundfile nor phonetics are available.
  */
  public function getPhonetic($v = null, $break = false, &$success = true){
    $ps = $this->getPhonetics($v);
    $success = (count($ps) == 0);
    if($break && count($ps) > 1){
      for($i = 0; $i < count($ps) - 1; $i++){
        $ps[$i]['break'] = true;
      }
    }
    return $ps;
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
      if(preg_match('/^Proto-/', $this->language->getShortName(false)))
        $proto = '*';
      //Returning
      if(strlen($s2) > 0) $altSpelling = $proto.$s2;
      if(strlen($s1) > 0) $altSpelling = $proto.$s1;
      if(isset($altSpelling)){
        $wTrans = $this->word->getWordTranslation($v, true, false);
        if($altSpelling != $wTrans){
          return $altSpelling;
        }
      }
      //If the Language is a RfcLanguage, we may return it's ModernName
      if($this->language->isRfcLanguage()){
        return $this->word->getModernName();
      }
      //If the Language has a RfcLanauge, we use that:
      if($l = $this->language->getRfcLanguage()){
        $t = self::getTranscriptionForWordLang($this->word, $l);
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
    $translation = $this->word->getWordTranslation($v);
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
  /**
    Because I'd like to create fewer objects, the former TranscriptionFromWordLang
    shall now be produced only by the following factory method:
  */
  private static $transcriptionMemo = array();
  public static function getTranscriptionForWordLang($word, $language){
    if(!isset($word))
      Config::error('Invalid Word in TranscriptionFromWordLang');
    if(!isset($language))
      Config::error('Invalid Language in TranscriptionFromWordLang');
    //Check existence in $transcriptionMemo:
    $k = $word->getId().'<>'.$language->getId();
    if(array_key_exists($k, self::$transcriptionMemo)){
      return self::$transcriptionMemo[$k];
    }
    $t = new Transcription();
    $t->word     = $word;
    $t->language = $language;
    $t->sid      = $language->getStudy()->getId();
    self::$transcriptionMemo[$k] = $t;
    return $t;
  }
}
?>
