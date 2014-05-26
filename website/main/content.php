<?php
require_once 'Tabulator.php';
//Making sure we have a valueManager:
if(!isset($valueManager)){
  require_once 'config.php';
  require_once 'valueManager/RedirectingValueManager.php';
  $dbConnection = Config::getConnection();
  $valueManager = RedirectingValueManager::getInstance();
}
$v = $valueManager;
$t = $v->getTranslator();
//Building the content:
$content = array(
  'isWordView'            => $v->gpv()->isView('WordView')
, 'isLanguageView'        => $v->gpv()->isView('LanguageView')
, 'isMapView'             => $v->gpv()->isView('MapView')
, 'isMultiView'           => $v->gpv()->isView('MultiView')
, 'isMultiViewTransposed' => $v->gpv()->isView('MultiTransposed')
, 'isWhoAreWeView'        => $v->gpv()->isView('WhoAreWeView')
);
$tabulator = new Tabulator($v);
if($content['isWordView']){
  if($words = $v->getWords()){
    $content['WordTable'] = $tabulator->tabulateWord(current($words));
  }else{
    Config::error("Got no single word to display. (PageView)");
  }
}else if($content['isLanguageView']){
  if($ls = $v->getLanguages()){
      $content['LanguageTable'] = $tabulator->languageTable(current($ls));
  }else{
    Config::error("Got no single language to display. (PageView)");
  }
}else if($content['isMapView']){
  $t = $v->getTranslator();
  $mapView = array(
    'WordHeadline' => $tabulator->wordHeadline(current($v->getWords()))
  , 'viewAll'  => $v->setLanguages($v->getStudy()->getLanguages())->link()
  , 'viewLast' => $v->setLanguages()->link()
  , 'allSelected' => $v->hasAllLanguages()
  , 'mapsMenuToggleShow' => $t->st('maps_menu_toggleShow')
  , 'mapsMenuViewAll'    => $t->st('maps_menu_viewAll')
  , 'mapsMenuViewLast'   => $t->st('maps_menu_viewLast')
  , 'mapsMenuCenterMap'  => $t->st('maps_menu_centerMap')
  , 'mapsMenuCoreRegion' => $t->st('maps_menu_viewCoreRegion')
  , 'mapsMenuPlayNs'     => $t->st('maps_menu_playNs')
  , 'mapsMenuPlaySn'     => $t->st('maps_menu_playSn')
  , 'mapsMenuPlayWe'     => $t->st('maps_menu_playWe')
  , 'mapsMenuPlayEw'     => $t->st('maps_menu_playEw')
  , 'mapsData' => $tabulator->mapsData()
  );
  $mapView['lastSelected'] = !$mapView['allSelected'];
  $content['MapView'] = $mapView;
}else if($content['isMultiView']){
  $content['Multitable'] = tables_multiwordTable($v);
}else if($content['isMultiViewTransposed']){
  $content['MultitableTransposed'] = tables_multiwordTableTransposed($v);
}else if($v->gpv()->isView('WhoAreWeView')){
  require_once 'about/contributors.php';
  $content['contributors'] = $conts;
}
?>
