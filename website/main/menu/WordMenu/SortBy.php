<?php
/**
  @param $v ValueManager
  @param $t Translator
  @return sortBy String Html
  Builds the sortBy block for WordMenu.php
*/
function WordMenuBuildSortBy($v, $t){
  $sortBy = $t->st('menu_words_selectortext');
  $lOrder = $t->st('menu_words_sortelicitation');
  $aOrder = $t->st('menu_words_selector_rfclangs');
  if($v->gwo()->isLogical()){
    $href    = $v->gwo()->setAlphabetical()->link('', 'data-href');
    $content = "<label class='radio'><input type='radio' $href>$aOrder</label>"
             . "<label class='radio'><input type='radio' checked>$lOrder</label>";
  }else{
    $href    = $v->gwo()->setLogical()->link('', 'data-href');
    $content = "<label class='radio'><input type='radio' checked>$aOrder</label>"
             . "<label class='radio'><input type='radio' $href>$lOrder</label>";
  }
  return "<h5 id='sortBy'><img src='img/sort-icon.png' class='icon'>$sortBy$content</h6>";
}
?>
