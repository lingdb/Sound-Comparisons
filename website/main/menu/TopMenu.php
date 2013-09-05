<?php
/**
  The Menu creates lists of things for the user.
  To do so, it needs access to the ValueManager.
*/
//Including means to color the topmenu:
require_once 'TopMenu/Color.php';
//Making sure we have a valueManager:
if(!isset($valueManager)){
  chdir('..');
  require_once 'config.php';
  require_once 'valueManager/RedirectingValueManager.php';
  $dbConnection = $config->getConnection();
  $valueManager = new RedirectingValueManager($dbConnection, $config);
}
//I like shorter names:
$v = $valueManager;
$t = $v->getTranslator();
/**
  @param id String
  @param entries String[][] A list of Tuples of (href, content, title)
  @param [noTopLink = false] Bool if true, .topLink won't be used, but the element will appear active instead.
  @return dropDown String
*/
function buildDropdown($id, $entries, $noTopLink = false){
  $active = $noTopLink ? ' class="active"' : '';
  $topLnk = $noTopLink ? '' : ' topLink';
  $id = $id !== '' ? " id='$id'" : '';
  $dropDown = "<ul$id class='nav'><li$active>";
  $head = array_shift($entries);
  $href = $head[0] !== '' ? ' '.$head[0] : '';
  $cont = $head[1];
  $titl = $head[2] !== '' ? ' title="'.$head[2].'"' : '';
  $dropDown .= "<a class='dropdown-toggle$topLnk' data-toggle='dropdown'$href$titl><i class='icon-dropdown-custom'></i>$cont</a>"
             . "<ul class='dropdown-menu'>";
  foreach($entries as $e){
    $href = $e[0] !== '' ? ' '.$e[0] : '';
    $cont = $e[1];
    $titl = $e[2] !== '' ? ' title="'.$e[2].'"' : '';
    $dropDown .= "<li><a$href$titl>$cont</a></li>";
  }
  return $dropDown.'</ul></li></ul>';
}
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
//First segment of the navbar:
$topmenu = '<div class="navbar myflow row-fluid" id="topMenu"><div class="navbar-inner span2">';
//The Logo:
$topmenu .= '<div id="logo"><a target="_blank" title="'
          . $t->st('website_logo_hover')
          . '" href="http://www.eva.mpg.de/lingua/"><img src="img/logo.png"/></a></div>';
//Studies:
$studies  = array();
// The current study:
array_push($studies, array('', $v->getStudy()->getName($v), ''));
//Possible studies:
foreach($v->gsm()->getStudies() as $s){
  $href = $v->gwo()->clear()->setRegions()->setLanguages()->setWords()->setStudy($s)->link();
  $content = $s->getName($v);
  if($s->getId() === $v->getStudy()->getId())
    continue;
  array_push($studies, array($href, $content, ''));
}
$topmenu .= buildDropdown('topmenuFamilies', $studies);
//Middle segment of the navbar:
$topmenu .= '</div><div class="navbar-inner span8">';
//Pageviews:
$topmenu  .= '<div class="brand">'.$t->st('topmenu_views').':</div>';
$pv = $v->gpv();
//Other views:
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
//Adding the elements:
$topmenu .= '<ul class="nav nav-tabs">';
$topmenu .= buildPageView($pv->isMapView(),         $mapView,      tColor(COLOR_M, $names['m']),  $hovers['m'], 'maps.png');
$topmenu .= buildPageView($pv->isSingleView(),      $singleView,   tColor(COLOR_W, $names['w']),  $hovers['w'],   '1w.png');
$topmenu .= buildPageView($pv->isLanguageView(),    $languageView, tColor(COLOR_L, $names['l']),  $hovers['l'],   '1l.png');
$topmenu .= buildPageView($pv->isMultiView(),       $multiView,    tColor(COLOR_LW,$names['lw']), $hovers['lw'],  'lw.png');
$topmenu .= buildPageView($pv->isMultiTransposed(), $multiViewT,   tColor(COLOR_WL,$names['wl']), $hovers['wl'],  'wl.png');
$topmenu .= '</ul>';
//Downloads:
$topmenu .= buildDropdown('topmenuDownloads', array(
  array('','<i class="icon-download-alt"></i>', $t->st('topmenu_download_title'))
, array($v->link('export/csv.php'), $t->st('topmenu_download_csv'),'')
, array($v->link('export/soundfiles.php').' target="_blank"', $t->st('topmenu_download_zip'),'')
));
//Starting the right part of the topMenu:
$topmenu .= '</div><div class="navbar-inner span2">';
//Languages:
$languages = array(array('', $t->showFlag(), ''));
//Other options:
foreach($t->getOthers() as $ot){
  $href = $v->setTranslator($ot)->link();
  $flag = $ot->showFlag();
  $name = $ot->showName();
  array_push($languages, array($href, "$flag$name", ''));
}
$topmenu .= buildDropdown('topmenuSiteLang', $languages);
//SoundPlayOptions
$ttip  = $t->st('topmenu_soundoptions_tooltip');
$hover = $t->st('topmenu_soundoptions_hover');
$click = $t->st('topmenu_soundoptions_click');
$topmenu .= "<ul id='topmenuSoundOptions' class='nav nav-tabs' title='$ttip'><li>"
  . "<i class='icon-eject rotate90'></i>"
  . "<div class='btn-group'>"
  . "<button type='button' value='hover' class='btn btn-mini btn-inverse' disabled title='$hover'><img src='img/hover.png'></button>"
  . "<button type='button' value='click' class='btn btn-mini' title='$click'>Click</button>"
  . "</div></li></ul>";
//About:
$about = array(
  array('','<img src="img/info.png">',$t->st('topmenu_about'))
, array($t->st('topmenu_about_whoarewe_href'),    $t->st('topmenu_about_whoarewe'),    '')
, array($t->st('topmenu_about_furtherinfo_href'), $t->st('topmenu_about_furtherinfo'), '')
, array($t->st('topmenu_about_research_href'),    $t->st('topmenu_about_research'),    '')
, array($t->st('topmenu_about_contact_href'),     $t->st('topmenu_about_contact'),     '')
);
$topmenu .= buildDropdown('topmenuAbout', $about);
//topmenu finish:
echo $topmenu.'</div></div>';
?>
