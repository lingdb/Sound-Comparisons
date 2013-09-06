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
<div class="brand"><? echo $title; ?></div>
<?php
//Views:
if($pv->isSingleView()){
  $mapView    = $v->gpv()->setMapView()->setRegions()->setLanguages()->unsetUserCleaned()->link();
}else
  $mapView    = $v->gpv()->setMapView()->setRegions()->setLanguages()->setWords()->unsetUserCleaned()->link();
if($pv->isMapView()){
  $singleView = $v->gpv()->setSingleView()->setLanguages()->unsetUserCleaned()->link();
}else
  $singleView = $v->gpv()->setSingleView()->setWords()->setLanguages()->unsetUserCleaned()->link();
$languageView = $v->gpv()->setLanguageView()->setWords()->setLanguages()->unsetUserCleaned()->link();
$multiView    = $v->gpv()->setMultiView()->setRegions()->setLanguages()->setWords()->unsetUserCleaned()->link();
$multiViewT   = $v->gpv()->setMultiTransposed()->setRegions()->setLanguages()->setWords()->unsetUserCleaned()->link();
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
echo buildPageView($pv->isMapView(),         $mapView,      tColor(COLOR_M, $names['m']),  $hovers['m'], 'maps.png');
echo buildPageView($pv->isSingleView(),      $singleView,   tColor(COLOR_W, $names['w']),  $hovers['w'],   '1w.png');
echo buildPageView($pv->isLanguageView(),    $languageView, tColor(COLOR_L, $names['l']),  $hovers['l'],   '1l.png');
echo buildPageView($pv->isMultiView(),       $multiView,    tColor(COLOR_LW,$names['lw']), $hovers['lw'],  'lw.png');
echo buildPageView($pv->isMultiTransposed(), $multiViewT,   tColor(COLOR_WL,$names['wl']), $hovers['wl'],  'wl.png');
?>
</ul>
