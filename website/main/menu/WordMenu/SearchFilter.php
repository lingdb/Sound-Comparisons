<?php
/**
  @param $v ValueManager
  @param $t Translator
  @return $searchFilter []
  Builds the search/filter block for WordMenu.php
*/
function WordMenuBuildSearchFilter($v, $t){
$study        = $v->getStudy();
$searchFilter = array(
  'sfby'         => $t->st('menu_words_filter_head')
, 'spelling'     => $t->st('menu_words_filterspelling')
, 'phonetics'    => $t->st('menu_words_filterphonetics')
, 'soundPath'    => Config::$soundPath
, 'psTarget'     => $t->st('menu_words_filter_regex_link')
, 'psHover'      => $t->st('menu_words_filter_regex_hover')
, 'in'           => $t->st('menu_words_filter_spphin')
, 'ipaOpenTitle' => $t->st('menu_words_open_ipaKeyboard')
);
//Filling spList:
$spList = array(
  'current' => ($spL = $v->gwo()->getSpLang())
             ? $spL->getSpellingName()
             : $v->gtm()->showName(true)
, 'options' => array()
);
$id = ($spL = $v->gwo()->getSpLang()) ? $spL->getId() : -1;
if($id !== -1){
  $href = $v->gwo()->setSpLang()->link('','data-href');
  $name = $v->gtm()->showName(true);
  if($name !== $spList['current']){
    array_push($spList['options'], array(
      'link' => $href
    , 'name' => $name)
    );
  }
}
foreach($study->getSpellingLanguages() as $sl){
  if($sl->getId() != $id){
    $href = $v->gwo()->setSpLang($sl)->link('','data-href');
    $name = $sl->getSpellingName();
    array_push($spList['options'], array(
      'link' => $href
    , 'name' => $name)
    );
  }
}
$searchFilter['spList'] = $spList;
//Making sure these are defined to avoid warnings:
$phUl = '';
//Filling phList:
$phList = array(
  'current' => $v->gwo()->getPhLang()->getShortName(false)
, 'options' => array()
);
$id = $v->gwo()->getPhLang()->getId();
foreach($study->getLanguages() as $pl){
  if($pl->getId() !== $id){
    $href = $v->gwo()->setPhLang($pl)->link('','data-href');
    $name = $pl->getShortName(false);
    array_push($phList['options'], array(
      'link' => $href
    , 'name' => $name)
    );
  }
}
$searchFilter['phList'] = $phList;
//The addAll/clearAll buttons:
if($v->gpv()->isSelection()){
  $searchFilter = array_merge($searchFilter, array(
    'filterFoundWords' => $t->st('menu_words_filterFoundWords')
  , 'fTitle'           => $t->st('menu_words_filterTitleMultiWords').':'
  , 'fAddAll'          => $t->st('menu_words_filterAddMultiWords')
  , 'fRefresh'         => $t->st('menu_words_filterRefreshMultiWords')
  , 'fClearAll'        => $t->st('menu_words_filterClearAllWords')
  , 'fClearAllLink'    => $v->setWords()->link('','data-href')
  ));
}
//Done:
return $searchFilter;
}
?>
