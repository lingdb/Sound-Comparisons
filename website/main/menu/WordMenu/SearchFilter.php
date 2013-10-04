<?php
/**
  @param $v ValueManager
  @param $t Translator
  @return search/filter String Html
  Builds the search/filter block for WordMenu.php
*/
function WordMenuBuildSearchFilter($v, $t){
$study        = $v->getStudy();
$sfby         = $t->st('menu_words_filter_head');
$spelling     = $t->st('menu_words_filterspelling');
$phonetics    = $t->st('menu_words_filterphonetics');
$sp           = Config::$soundPath;
$phonetics   .= " <div class='audio'>"
              . "[<div class='transcription'>fəˈnɛtɪks</div>]"
              . "<audio autobuffer='' preload='auto'>"
              . "<source src='$sp/fonetiks.ogg'></source>"
              . "<source src='$sp/fonetiks.mp3'></source>"
              . "</audio></div>";
$psTarget     = $t->st('menu_words_filter_regex_link');
$psHover      = $t->st('menu_words_filter_regex_hover');
$powersearch  = "<a href='$psTarget' title='$psHover' target='_blank'><i class='icon-question-sign'></i></a>";
$in           = $t->st('menu_words_filter_spphin');
$ipaOpenTitle = $t->st('menu_words_open_ipaKeyboard');
//Filling spUl:
$spName = ($spL = $v->gwo()->getSpLang()) ? $spL->getSpellingName() : $v->gtm()->showName(true);
$spList = "<select><option selected='selected'>$spName</option>";
$id = ($spL = $v->gwo()->getSpLang()) ? $spL->getId() : -1;
if($id !== -1){
  $href = $v->gwo()->setSpLang()->link('','data-href');
  $name = $v->gtm()->showName(true);
  if($name !== $spName)
    $spList .= "<option $href>$name</option>";
}
foreach($study->getSpellingLanguages() as $sl){
  if($sl->getId() != $id){
    $href    = $v->gwo()->setSpLang($sl)->link('','data-href');
    $name    = $sl->getSpellingName();
    $spList .= "<option $href>$name</option>";
  }
}
$spList .= '</select>';
//Making sure these are defined to avoid warnings:
$phUl = '';
//Filling phUl:
$phName = $v->gwo()->getPhLang()->getShortName(false);
$phList = "<select><option selected='selected'>$phName</option>";
$id = $v->gwo()->getPhLang()->getId();
foreach($study->getLanguages() as $pl){
  if($pl->getId() !== $id){
    $href    = $v->gwo()->setPhLang($pl)->link('','data-href');
    $name    = $pl->getShortName(false);
    $phList .= "<option $href>$name</option>";
  }
}
$phList .= '</select>';
//The addAll/clearAll buttons:
if($v->gpv()->isSelection()){
  $fTitle    = $t->st('menu_words_filterTitleMultiWords').':';
  $fAddAll   = $t->st('menu_words_filterAddMultiWords');
  $fRefresh  = $t->st('menu_words_filterRefreshMultiWords');
  $fClearAll = $t->st('menu_words_filterClearAllWords');
  $fAddAll   = '<i class="icon-plus" id="FilterAddMultiWords" title="'.$fAddAll.'"></i>';
  $fRefresh  = '<i class="icon-repeat" id="FilterRefreshMultiWords" title="'.$fRefresh.'"></i>';
  $fClearAll = '<i class="icon-remove" title="'.$fClearAll.'" '.$v->setWords()->link('','data-href').'></i>';
}else{ $fTitle = $fAddAll = $fRefresh = $fClearAll = ''; }
$buttons = '<tr id="FilterAddAll">'
         . '<td>'
         . $t->st('menu_words_filterFoundWords')
         . ':<code id="FilterFoundMultiWords">x</code></td>'
         . "<td>$fTitle$fAddAll$fRefresh$fClearAll</td>"
         . '</tr>';
//Composing:
return "<table id='wordlistfilter' class='table-bordered table-striped'>"
     . "<tr><td id='searchFilterTitle' class='userOption'><i class='icon-search'></i>$sfby:</td>"
     . "<td id='Powersearch'>$powersearch</td></tr>"
     . "<tr><td id='SpellingTitle'>$spelling $in</td>"
     . "<td id='PhoneticTitle'>$phonetics $in</td></tr>"
     . "<tr><td>$spList</td><td>$phList</td></tr>"
     . "<tr><td><input id='SpellingFilter' type='text'/></td>"
     . "<td><input id='PhoneticFilter' type='text'/>"
     . "<a id='IPAOpenKeyboard' class='superscript' title='$ipaOpenTitle'>IPA</a>"
     . "</td></tr>$buttons</table>";
}
?>
