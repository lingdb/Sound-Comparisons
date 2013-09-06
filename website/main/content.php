<?php
require_once 'Tabulator.php';
//Making sure we have a valueManager:
if(!isset($valueManager)){
  require_once 'config.php';
  require_once 'valueManager/RedirectingValueManager.php';
  $dbConnection = $config->getConnection();
  $valueManager = new RedirectingValueManager($dbConnection, $config);
}
$v = $valueManager;
//Building the content:
$s = $valueManager->gpv()->isView('MapView') ? ' style="bottom:2em;"' : '';
echo "<div id='contentArea'$s class='span8'>";
$tabulator = new Tabulator($v);
if($v->gpv()->isView('WordView')){
  if($words = $v->getWords()){
    $tabulator->tabluateWord(current($words));
  }else{
    die("Got no single word to display. (PageView)");
  }
}else if($v->gpv()->isView('LanguageView')){
  if($ls = $v->getLanguages()){
      $tabulator->languageTable(current($ls));
  }else{
    die("Got no single language to display. (PageView)");
  }
}else if($v->gpv()->isView('MapView')){
  $t        = $v->getTranslator();
  $headline = $tabulator->wordHeadline(current($v->getWords()));
  $headline = "<table id='map_headline' class='table'>$headline</table>"; //FIXME this is kind of dirty
  $viewAll  = $v->setLanguages($v->getStudy()->getLanguages())->link();
  $viewLast = $v->setLanguages()->link();
  $allSelected  = '';
  $lastSelected = '';
  if($v->hasAllLanguages()){
    $allSelected  = ' class="selected"';
  }else{
    $lastSelected = ' class="selected"';
  }
  $mapsMenuToggleShow = $t->st('maps_menu_toggleShow');
  $mapsMenuViewAll    = $t->st('maps_menu_viewAll');
  $mapsMenuViewLast   = $t->st('maps_menu_viewLast');
  $mapsMenuCenterMap  = $t->st('maps_menu_centerMap');
  $mapsMenuCoreRegion = $t->st('maps_menu_viewCoreRegion');
  $mapsMenuPlayNs     = $t->st('maps_menu_playNs');
  $mapsMenuPlaySn     = $t->st('maps_menu_playSn');
  $mapsMenuPlayWe     = $t->st('maps_menu_playWe');
  $mapsMenuPlayEw     = $t->st('maps_menu_playEw');
  $mapsMenu = "<div id='map_menu_toggle'>$mapsMenuToggleShow: "
            . "<a id='map_menu_viewAll' $viewAll$allSelected>$mapsMenuViewAll</a> | "
            . "<a id='map_menu_viewLast' $viewLast$lastSelected>$mapsMenuViewLast</a>"
            . "</div>"
            . "<div id='map_zoom_options'>"
            . "<a id='map_menu_zoomCenter'>$mapsMenuCenterMap</a> | "
            . "<a class='selected' id='map_menu_zoomCoreRegion'>$mapsMenuCoreRegion</a>"
            . "</div>"
            . "<div id='map_play_directions'>"
            . "<i data-direction='ns' title='$mapsMenuPlayNs' class='icon-eject rotate180'></i>"
            . "<i data-direction='sn' title='$mapsMenuPlaySn' class='icon-eject'></i>"
            . "<i data-direction='we' title='$mapsMenuPlayWe' class='icon-eject rotate90'></i>"
            . "<i data-direction='ew' title='$mapsMenuPlayEw' class='icon-eject rotate270'></i>"
            . "</div>";
  $mapsData = $tabulator->mapsData();
  echo "$headline<div id='map_menu'>$mapsMenu</div><div id='map_canvas'></div><div id='map_data'>$mapsData</div>";
}else if($v->gpv()->isSelection()){
  $tabulator->multiwordTable($v->gpv()->isView('MultiTransposed'));
}else if($v->gpv()->isView('WhoAreWeView')){
  include 'about/contributors.php';
}
echo "</div>";
?>
