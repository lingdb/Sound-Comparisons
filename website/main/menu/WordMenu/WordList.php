<?php
/**
  @param $words Word[]
  @param $v ValueManager
  @param $t Translator
  @return $wordList []
  Builds the WordList for WordMenu.php
*/
function WordMenuBuildWordList($words, $v, $t){
  Stopwatch::start('WordMenuBuildWordList');
  $wordList = array('words' => array());
  $multi    = $v->gpv()->isSelection();
  foreach($words as $w){
    $hasWord = $v->hasWord($w);
    $word = array(
      'selected' => $hasWord
    , 'cname'    => $w->getKey()
    , 'trans'    => $w->getTranslation($v, true, false)
    , 'ttip'     => $w->getLongName()
    );
    //The icon:
    if($multi){
      $icon = $hasWord ? 'icon-check' : 'icon-chkbox-custom';
      $ttip = $hasWord ? $t->st('multimenu_tooltip_del')
                       : $t->st('multimenu_tooltip_add');
      $href = $hasWord ? $v->delWord($w)->link()
                       : $v->addWord($w)->link();
      $word['icon'] = "<a title='$ttip' $href><i class='$icon'></i></a>";
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
      $subscript = '';
      if(count($phonetics) > 1){
        $ttip = $t->st('tooltip_subscript_differentVariants');
        $subscript = '<div class="subscript" title="'.$ttip.'">'.($i+1).'</div>';
      }
      array_push($wordList['words'], array_merge($word, array(
        'subscript' => $subscript
      , 'phonetic'  => $phonetic
      )));
    }
  }
  Stopwatch::stop('WordMenuBuildWordList');
  return $wordList;
}
?>
