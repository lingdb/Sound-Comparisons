<?php
/**
  @param $words Word[]
  @param $v ValueManager
  @param $t Translator
  @return wordList String Html
  Builds the WordList for WordMenu.php
*/
function WordMenuBuildWordList($words, $v, $t){
  $wordList = "<ul class='wordList'>";
  $multi    = $v->gpv()->isSelection();
  foreach($words as $w){
    $hasWord  = $v->hasWord($w);
    $selected = $hasWord ? ' class="selected"' : '';
    $icon = '';
    $ttip = '';
    $href = '';
    if($multi){
      $icon = $hasWord ? 'icon-check' : 'icon-chkbox-custom';
      $icon = "<i class='$icon'></i>";
      $ttip = $hasWord ? $t->st('multimenu_tooltip_del')
                       : $t->st('multimenu_tooltip_add');
      $href = $hasWord ? $v->delWord($w)->link()
                       : $v->addWord($w)->link();
    }else if(!$hasWord){
      if($v->gpv()->isMapView()){
        $href = $v->setWord($w)->link();
        $ttip = $t->st('menu_words_tooltip_choose_map');
      }else{
        $href = $v->gpv()->setSingleView()->setWord($w)->link();
        $ttip = $t->st('menu_words_tooltip_choose_single');
      }
    }
    $phonetics = array('*'.$w->getProtoName());
    if($rf = $v->gwo()->getPhLang()){
      $tr  = new TranscriptionFromWordLang($w, $rf);
      $phonetics = $tr->getPhonetics($v);
    }
    $trans = $w->getTranslation($v, true);
    $cname = $w->getKey();
    $ttip  = ($ttip === '') ? '' : " title='$ttip'";
    $link  = ($href === '') ? $trans : "<a class='color-word wordLinkModernName'$ttip $href>$icon$trans</a>";
    foreach($phonetics as $i => $phonetic){
      $subscript = '';
      if(count($phonetics) > 1){
        $ttip = $t->st('tooltip_subscript_differentVariants');
        $subscript = '<div class="subscript" title="'.$ttip.'">'.($i+1).'</div>';
      }
      $wordList .= "<li$selected>"
                 . "<div class='p50 color-word' data-canonicalName='$cname'>"
                 . "$link$subscript"
                 . "</div><div class='p50'>$phonetic</div></li>";
    }
  }
  return $wordList.'</ul>';
}
?>
