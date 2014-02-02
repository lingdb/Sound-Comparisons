<?php
require_once 'Color.php';
/**
  @param $active Bool
  @param $href String
  @param $content String
  @param $title String
  @return $li String
*/
function buildPageView($active, $href, $content, $title, $img){
  $img = $img ? "<img class='pvImg' src='img/$img'>" : '';
  $topLnk = $active ? '' : ' class="topLink"';
  $active = $active ? ' class="active"' : '';
  $href   = $href !== '' ? ' '.$href : '';
  return "<li$active><a$href$topLnk title='$title'>$img$content</a></li>";
}
$v     = $valueManager;
$t     = $v->getTranslator();
$pv    = $v->gpv();
$title = $t->st('topmenu_views');
?>
<ul class="nav nav-tabs">
<div class="brand"><?php echo $title; ?></div>
<?php
//Views:
if($pv->isView('WordView')){
  $mapView    = $v->gpv()->setView('MapView')->setRegions()->setLanguages()->unsetUserCleaned()->link();
}else
  $mapView    = $v->gpv()->setView('MapView')->setRegions()->setLanguages()->setWords()->unsetUserCleaned()->link();
if($pv->isView('MapView')){
  $singleView = $v->gpv()->setView('WordView')->setLanguages()->unsetUserCleaned()->link();
}else
  $singleView = $v->gpv()->setView('WordView')->setWords()->setLanguages()->unsetUserCleaned()->link();
$languageView = $v->gpv()->setView('LanguageView')->setWords()->setLanguages()->unsetUserCleaned()->link();
$multiView    = $v->gpv()->setView('MultiView')->setRegions()->setLanguages()->setWords()->unsetUserCleaned()->link();
$multiViewT   = $v->gpv()->setView('MultiTransposed')->setRegions()->setLanguages()->setWords()->unsetUserCleaned()->link();
//Translations:
$hovers = array(
    'm'  => $t->st('topmenu_views_mapview_hover')
  , 'w'  => $t->st('topmenu_views_wordview_hover')
  , 'l'  => $t->st('topmenu_views_languageview_hover')
  , 'lw' => $t->st('topmenu_views_multiview_hover')
  , 'wl' => $t->st('topmenu_views_multitransposed_hover'));
$names = array(
    'm'  => $t->st('topmenu_views_mapview')
  , 'w'  => $t->st('topmenu_views_wordview')
  , 'l'  => $t->st('topmenu_views_languageview')
  , 'lw' => $t->st('topmenu_views_multiview')
  , 'wl' => $t->st('topmenu_views_multitransposed'));
echo buildPageView($pv->isView('MapView'),         $mapView,      tColor(COLOR_M, $names['m']),  $hovers['m'], 'maps.png');
echo buildPageView($pv->isView('WordView'),        $singleView,   tColor(COLOR_W, $names['w']),  $hovers['w'],   '1w.png');
echo buildPageView($pv->isView('LanguageView'),    $languageView, tColor(COLOR_L, $names['l']),  $hovers['l'],   '1l.png');
echo buildPageView($pv->isView('MultiView'),       $multiView,    tColor(COLOR_LW,$names['lw']), $hovers['lw'],  'lw.png');
echo buildPageView($pv->isView('MultiTransposed'), $multiViewT,   tColor(COLOR_WL,$names['wl']), $hovers['wl'],  'wl.png');
?>
</ul>
