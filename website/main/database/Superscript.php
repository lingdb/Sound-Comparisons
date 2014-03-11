<?php
/**
  The Superscript class shall provide static methods to aid fetching superscripts.
*/
class Superscript {
  /**
    @param $index Either String Integer
    $index may either be the name of the column in the Transcriptions_* tables,
    or the numeric Ix for the TranscrSuperscriptInfo table.
    This method is only used by Transcription:getSuperscriptInfo.
    @return [Abbreviation, HoverText] [String]
  */
  public static function forTranscription($index){
    $t = RedirectingValueManager::getInstance()->getTranslator();
    if(is_numeric($index)){
      if($r = $t->getTranscrSuperscriptTranslation($index)){
        return $r;
      }
      $q = 'SELECT Abbreviation, HoverText FROM TranscrSuperscriptInfo WHERE Ix = '.$index;
      if($r = Config::getConnection()->query($q)->fetch_row()){
        return $r;
      }
    }else if(strlen($index) === 3){ // The ISOCode case
      if($r = $t->getTranscrSuperscriptTranslation($index)){
        return $r;
      }
      $q = "SELECT Abbreviation, FullNameForHoverText FROM TranscrSuperscriptLenderLgs WHERE IsoCode = '$index'";
      if($r = Config::getConnection()->query($q)->fetch_row()){
        return $r;
      }
    }else{
      switch($index){
        case 'NotCognateWithMainWordInThisFamily':
          return self::forTranscription(1);
        case 'CommonRootMorphemeStructDifferent':
          return self::forTranscription(2);
        case 'DifferentMeaningToUsualForCognate':
          return self::forTranscription(3);
        case 'ActualMeaningInThisLanguage':
          return self::forTranscription(11);
        case 'OtherLexemeInLanguageForMeaning':
          return self::forTranscription(12);
        case 'RootIsLoanWordFromKnownDonor':
          return self::forTranscription(21);
        case 'RootSharedInAnotherFamily':
          return self::forTranscription(22);
      }
    }
    Config::error("Could not resolve Superscript:forTranscription($index).");
  }
}
?>
