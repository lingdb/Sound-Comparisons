<?php
/**
  The standalone WordMenu that let's me get rid of the WordStrategy
  and thereby simplify the code.
*/
//WordMenu specific requirements:
require_once 'WordMenu/SortBy.php';
require_once 'WordMenu/SearchFilter.php';
require_once 'WordMenu/WordList.php';
//Making sure we have a valueManager:
if(!isset($valueManager)){
  chdir('..');
  require_once 'config.php';
  require_once 'valueManager/RedirectingValueManager.php';
  $dbConnection = Config::getConnection();
  $valueManager = RedirectingValueManager::getInstance();
}
//Some setup:
$v = $valueManager;
$t = $v->getTranslator();
$study = $v->getStudy();
//Starting to build it:
$wordMenu = array(
  'title'        => $t->st('menu_words_words')
, 'sortBy'       => WordMenuBuildSortBy($v, $t)
, 'searchFilter' => WordMenuBuildSearchFilter($v, $t)
);
//The meaningsets block:
if($v->gwo()->isLogical()){
  $wordMenu['isLogical'] = true;
  //Used for the meaningsets block:
  $wordMenu['meaningSets'] = $t->st('menu_words_meaningSets_title');
  $wordMenu['expand']      = $t->st('menu_words_meaningSets_expand');
  $wordMenu['collapse']    = $t->st('menu_words_meaningSets_collapse');
  $wordMenu['ahref']       = $v->setMeaningGroups($study->getMeaningGroups())->link();
  $wordMenu['nhref']       = $v->setMeaningGroups()->link();
  //Building the logical wordlist:
  $wordMenu['meaningGroups'] = array();
  $multi = $v->gpv()->isSelection();
  foreach($study->getMeaningGroups() as $mg){
    //Setup:
    $collapsed = !$v->hasMeaningGroup($mg);
    $mgWords = $mg->getWords();
    $mGroup = array(
      'name'     => $mg->getName()
    , 'fold'     => $collapsed ? 'mgFold' : 'mgUnfold'
    , 'triangle' => $collapsed ? 'icon-chevron-up rotate90' : 'icon-chevron-down'
    , 'link'     => $v->toggleMeaningGroup($mg)->link()
    );
    //Checkbox:
    if($multi){
      $has  = $v->gwm()->hasWords($mgWords);
      $icon = 'icon-chkbox-custom';
      switch($has){
        case 'all':
          $icon = 'icon-check';
          $href = $v->delWord($mgWords)->setUserCleaned()->link('','data-href');
          $ttip = $t->st('multimenu_tooltip_minus');
        break;
        case 'some':
          $icon = 'icon-chkbox-half-custom';
        case 'none':
          $href = $v->addWord($mgWords)->link('','data-href');
          $ttip = $t->st('multimenu_tooltip_plus');
      }
      $mGroup['checkbox'] = array(
        'link' => $href
      , 'ttip' => $ttip
      , 'icon' => $icon
      );
    }
    $mGroup['wordList'] = WordMenuBuildWordList($mgWords, $v, $t);
    array_push($wordMenu['meaningGroups'], $mGroup);
  }
}else{
  $wordMenu['wordList'] = WordMenuBuildWordList($study->getWords(), $v, $t);
}
//wordmenu finish:
echo Config::getMustache()->render('WordMenu', $wordMenu);
?>
