<?php
/**
  @param $v ValueManager
  @param $t Translator
  @return $sortBy []
  Builds the sortBy block for WordMenu.php
*/
function WordMenuBuildSortBy($v, $t){
  $logical = $v->gwo()->isLogical();
  $link    = $logical
           ? $v->gwo()->setAlphabetical()->link('', 'data-href')
           : $v->gwo()->setLogical()->link('', 'data-href');
  return array(
    'sortBy'    => $t->st('menu_words_selectortext')
  , 'isLogical' => $logical
  , 'aOrder'    => $t->st('menu_words_selector_rfclangs')
  , 'lOrder'    => $t->st('menu_words_sortelicitation')
  , 'link'      => $link
  );
}
?>
