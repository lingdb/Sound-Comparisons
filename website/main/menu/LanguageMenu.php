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
  $dbConnection = $config->getConnection();
  $valueManager = new RedirectingValueManager($dbConnection, $config);
}
//I like shorter names:
$v = $valueManager;
$t = $v->getTranslator();
//We only build the menu if we've got a Study:
if($study = $v->getStudy()){
  $languagemenu = '<div class="span2 well" id="leftMenu">';
  //The Headline:
  $rHeadline     = $t->st('menu_regions_headline');
  $languagemenu .= "<h3 class='color-language'>$rHeadline</h3>";
  //The collapse/expand all menu:
  $languageSets  = $t->st('menu_regions_languageSets_title').':';
  $collapseHref  = $v->setRegions($study->getRegions())->link();
  $collapseTitle = $t->st('menu_regions_languageSets_collapse');
  $expandHref    = $v->setRegions()->link();
  $expandTitle   = $t->st('menu_regions_languageSets_expand');
  $c = "<a $collapseHref title='$collapseTitle'><i class='icon-minus'></i></a>";
  $e = "<a $expandHref title='$expandTitle'><i class='icon-plus'></i></a>";
  $languagemenu .= "<h6>$languageSets$e$c</h6>";
  //The content:
  $showFlags = $config->getFlags();
  if($study->getColorByFamily()){
    $languagemenu .= '<dl class="familyList">';
    foreach($study->getFamilies() as $f){
      //Setup:
      $fName   = $f->getName();
      $fColor  = $f->getColor();
      $hasF    = $v->gfm()->hasFamily($f);
      $regions = $f->getRegions();
      $fHref   = $hasF ? $v->gfm()->delFamily($f)->link() : $v->gfm()->addFamily($f)->link();
      if(!count($regions)) continue;
      //Checkboxes:
      $checkbox = '';
      if($v->gpv()->isSelection()|| $v->gpv()->isMapView()){
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
        $checkbox = "<a $href title='$ttip'><i class='$icon'></i></a>";
      }
      //The RegionList:
      $regions = $hasF ? '' : LanguageMenuBuildRegionList($f->getRegions(), $v, $t, $showFlags);
      //Printing:
      $languagemenu .= "<dt style='background-color: #$fColor;'>"
                     . "$checkbox<a class='color-family' $fHref>$fName</a>"
                     . "</dt><dd>$regions</dd>";
    }
    $languagemenu .= '</dl>';
  }else{
    $languagemenu .= LanguageMenuBuildRegionList($study->getRegions(), $v, $t, $showFlags, true);
  }
  //languageMenu finish:
  echo $languagemenu.'</div>';
}
?>
