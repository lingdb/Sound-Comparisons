<?php
/**
  @param $words Word[]
  @param $v ValueManager
  @param $t Translator
  @return $wordList []
  Builds the WordList for WordMenu.php
*/
function WordMenuBuildWordList($words, $v, $t){
  $wordList = array('words' => array());
  $multi    = $v->gpv()->isSelection();
  foreach($words as $w){
    $hasWord = $v->hasWord($w);
    $word = array(
      'selected' => $hasWord
    , 'cname'    => $w->getKey()
    , 'trans'    => $w->getWordTranslation($v, true, false)
    , 'ttip'     => $w->getLongName()
    );
    //The icon:
    if($multi){
      $word['icon'] = array(
        'ttip' => $hasWord ? $t->st('multimenu_tooltip_del') : $t->st('multimenu_tooltip_add')
      , 'link' => $hasWord ? $v->delWord($w)->link() : $v->addWord($w)->link()
      , 'icon' => $hasWord ? 'icon-check' : 'icon-chkbox-custom'
      );
    }
    //The link:
    $word['link'] = $v->gpv()->isView('MapView')
                  ? $v->setWord($w)->link()
                  : $v->gpv()->setView('WordView')->setWord($w)->link();
    //Working the phonetics:
    $phonetics = array('*'.$w->getProtoName());
    if($rf = $v->gwo()->getPhLang()){
      $tr  = Transcription::getTranscriptionForWordLang($w, $rf);
      $phonetics = $tr->getPhonetics($v);
    }
    foreach($phonetics as $i => $phonetic){
      $subscript = null;
      if(count($phonetics) > 1){
        $subscript = array(
          'ttip'      => $t->st('tooltip_subscript_differentVariants')
        , 'subscript' => $i + 1
        );
      }
      array_push($wordList['words'], array_merge($word, array(
        'subscript' => $subscript
      , 'phonetic'  => $phonetic
      )));
    }
  }
  return $wordList;
}
?>
