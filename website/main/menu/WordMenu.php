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
//The headline:
$wordmenu = '<div id="rightMenu" class="span2 well">'
          . '<h3 class="color-word">'.$t->st('menu_words_words').'</h3>';
//Building the Filtermenu:
//The sortby block:
$wordmenu .= WordMenuBuildSortBy($v, $t);
//The search/filter block:
$wordmenu .= WordMenuBuildSearchFilter($v, $t);
//The meaningsets block:
$isLogical = $v->gwo()->isLogical();
if($isLogical){
  $all         = $study->getMeaningGroups();
  $none        = array(array_shift($all));
  $ahref       = $v->setMeaningGroups($all)->link();
  $nhref       = $v->setMeaningGroups($none)->link();
  $meaningSets = $t->st('menu_words_meaningSets_title');
  $collapse    = $t->st('menu_words_meaningSets_collapse');
  $expand      = $t->st('menu_words_meaningSets_expand');
  $wordmenu .= "<h6>$meaningSets:"
             . "<a title='$expand' $ahref><i class='icon-plus'></i></a>"
             . "<a title='$collapse' $nhref><i class='icon-minus'></i></a></h6>";
}
//Building the wordlist:
if($isLogical){
  $multi = $v->gpv()->isSelection();
  $wordmenu .= "<dl class='meaninggroupList'>";
  foreach($study->getMeaningGroups() as $mg){
    $collapsed = !$v->hasMeaningGroup($mg);
    if($mg->getId() == 1) $collapsed = !$collapsed;
    $class = $collapsed ? 'class = "mgFold"' : 'class = "mgUnfold"';
    $name  = $mg->getName();
    $checkbox = '';
    if($multi){
      $has  = $v->gwm()->hasWords($mg->getWords());
      $icon = 'icon-chkbox-custom';
      switch($has){
        case 'all':
          $icon = 'icon-check';
          $href = $v->delWord($mg->getWords())->setUserCleaned()->link('','data-href');
          $ttip = $t->st('multimenu_tooltip_minus');
        break;
        case 'some':
          $icon = 'icon-chkbox-half-custom';
        case 'none':
          $href = $v->addWord($mg->getWords())->link('','data-href');
          $ttip = $t->st('multimenu_tooltip_plus');
      }
      $checkbox = "<a $href title='$ttip'><i class='$icon'></i></a>";
    }
    $triangle = $collapsed ? 'icon-chevron-up rotate90' : 'icon-chevron-down';
    $href  = $v->toggleMeaningGroup($mg)->link();
    $title = "$checkbox<a class='color-meaninggroup' $href><i class='$triangle'></i>$name</a>";
    $wList = $collapsed ? '' : '<dd>'.WordMenuBuildWordList($mg->getWords(), $v, $t).'</dd>';
    $wordmenu .= "<dt $class>$title</dt>$wList";
  }
  $wordmenu .= "</dl>";
}else{
  $wordmenu .= WordMenuBuildWordList($study->getWords(), $v, $t);
}
//wordmenu finish:
echo $wordmenu.'</div>';
?>
