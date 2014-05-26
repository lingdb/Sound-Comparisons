<?php
/**
  The LanguageMenu is to be included in the site by the index.php.
  If I later on add js to only refresh the LanguageMenu,
  I'll be able to do so by calling the URL for it directly.
  By building the LanguageMenu as a file to be simply included,
  I hope to get rid of the too complicated Strategy Pattern in this case,
  aswell as achieve a lower memory footprint.
*/
//LanguageMenu specific requirements:
require_once 'LanguageMenu/RegionList.php';
//Making sure we have a valueManager:
if(!isset($valueManager)){
  chdir('..');
  require_once 'config.php';
  require_once 'valueManager/RedirectingValueManager.php';
  $dbConnection = Config::getConnection();
  $valueManager = RedirectingValueManager::getInstance();
}
//I like shorter names:
$v = $valueManager;
$t = $v->getTranslator();
//We only build the menu if we've got a Study:
if($study = $v->getStudy()){
  $languageMenu = array(
    'headline'      => $t->st('menu_regions_headline')
  , 'languageSets'  => $t->st('menu_regions_languageSets_title').':'
  , 'collapseHref'  => $v->setRegions($study->getRegions())->link()
  , 'collapseTitle' => $t->st('menu_regions_languageSets_collapse')
  , 'expandHref'    => $v->setRegions()->link()
  , 'expandTitle'   => $t->st('menu_regions_languageSets_expand')
  );
  //The content:
  $showFlags = Config::$flags_enabled;
  if($study->getColorByFamily()){
    $families = array();
    foreach($study->getFamilies() as $f){
      //Setup:
      $hasF = $v->gfm()->hasFamily($f);
      $regions = $f->getRegions();
      if(!count($regions)) continue;
      $family = array(
        'name'  => $f->getName()
      , 'color' => $f->getColor()
      , 'link'  => $hasF
                 ? $v->gfm()->delFamily($f)->link()
                 : $v->gfm()->addFamily($f)->link()
      );
      //Checkboxes:
      if($v->gpv()->isSelection()|| $v->gpv()->isView('MapView')){
        $languages = $f->getLanguages();
        $has  = $v->glm()->hasLanguages($languages);
        $icon = 'icon-chkbox-custom';
        switch($has){
          case 'all':
            $icon = 'icon-check';
            $href = $v->delLanguages($languages)->link('','data-href');
            $ttip = $t->st('multimenu_tooltip_del_family');
          break;
          case 'some':
            $icon = 'icon-chkbox-half-custom';
          case 'none':
            $href = $v->addLanguages($languages)->link('','data-href');
            $ttip = $t->st('multimenu_tooltip_add_family');
        }
        $family['checkbox'] = array(
          'link'  => $href
        , 'title' => $ttip
        , 'icon'  => $icon
        );
      }
      //The RegionList:
      if(!$hasF){
        $regions = LanguageMenuBuildRegionList($f->getRegions(), $v, $t, $showFlags);
        $family['RegionList'] = Config::getMustache()->render('RegionList', $regions);
      }
      $regions = $hasF ? '' : LanguageMenuBuildRegionList($f->getRegions(), $v, $t, $showFlags);
      //Adding the family:
      array_push($families, $family);
    }
    $languageMenu['families'] = $families;
  }else{
    $regions = LanguageMenuBuildRegionList($study->getRegions(), $v, $t, $showFlags, true);
    $languageMenu['RegionList'] = Config::getMustache()->render('RegionList', $regions);
  }
}
?>
