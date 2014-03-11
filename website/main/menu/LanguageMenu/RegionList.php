<?php
/**
  @param $regions Region[]
  @param $v ValueManager
  @param $t Translator
  @param [$showFlags = false]
  @param [$dl = false]
  @return regionList
  Builds the RegionList for LanguageMenu.php
*/
function LanguageMenuBuildRegionList($regions, $v, $t, $showFlags = false, $dl = false){
  $regionList = array(
    'isDl'    => $dl
  , 'regions' => array()
  );
  $colorByFamily = $v->getStudy()->getColorByFamily();
  foreach($regions as $r){
    $languages = $r->getLanguages($v->gpv()->isView('LanguageView'));
    if(count($languages) === 0)
      continue;
    //Basic region fields:
    $region = array(
      'selected'  => $v->hasRegion($r)
    , 'name'      => $r->getShortName()
    , 'ttip'      => $r->getName()
    , 'languages' => array()
    );
    //Dunno where these belong:
    $isMultiView = ($v->gpv()->isSelection() || $v->gpv()->isView('MapView'));
    $isMapView   = $v->gpv()->isView('MapView');
    //Building the checkbox:
    $icon = '';
    $ttip = '';
    $href = '';
    if($isMultiView){
      //Here we need to distinguish three cases:
      $has  = $v->glm()->hasLanguages($languages);
      $icon = 'icon-chkbox-custom';
      switch($has){
        case 'all':
          $icon = 'icon-check';
          $href = $v->delLanguages($languages)->link('','data-href');
          $ttip = $t->st('multimenu_tooltip_minus');
        break;
        case 'some':
          $icon = 'icon-chkbox-half-custom';
        case 'none':
          $href = $v->addLanguages($languages)->link('','data-href');
          $ttip = $t->st('multimenu_tooltip_plus');
      }
      $region['checkbox'] = array(
        'link' => $href
      , 'ttip' => $ttip
      , 'icon' => $icon
      );
    }
    //Color if it's not done by Family:
    if(!$colorByFamily)
      $region['color'] = $r->getColorStyle();
    //The Region link:
    $region['link'] = $region['selected']
                    ? $v->delRegion($r)->link()
                    : $v->addRegion($r)->link();
    //The triangle:
    $region['triangle'] = $region['selected']
                        ? 'icon-chevron-up rotate90'
                        : 'icon-chevron-down';
    //Languages for not selected Regions:
    if(!$region['selected']){
      foreach($languages as $l){
        $language = array(
          'shortName' => $l->getShortName()
        , 'longName'  => $l->getLongName(false)
        , 'selected'  => $v->hasLanguage($l)
        , 'link'      => $v->gpv()->setView('LanguageView')->setLanguage($l)->link()
        );
        if($showFlags)
          $language['flag'] = $l->getFlag();
        //Building the icon for a language:
        if($isMultiView){
          if($language['selected']){
            if($isMapView){
              $ttip = $language['longName']."\n".$t->st('multimenu_tooltip_del_map');
            }else{
              $ttip = $language['longName']."\n".$t->st('multimenu_tooltip_del');
            }
            $href = $v->delLanguage($l)->setUserCleaned()->link('','data-href');
          }else{
            if($isMapView){
              $ttip = $language['longName']."\n".$t->st('multimenu_tooltip_add_map');
            }else{
              $ttip = $language['longName']."\n".$t->st('multimenu_tooltip_add');
            }
            $href = $v->addLanguage($l)->link('','data-href');
          }
          $language['icon'] = array(
            'ttip'    => $ttip
          , 'link'    => $href
          , 'checked' => $language['selected']
                       ? 'icon-check'
                       : 'icon-chkbox-custom'
          );
        }
        array_push($region['languages'], $language);
      }
    }
    array_push($regionList['regions'], $region);
  }
//die(var_dump($regionList));
  return $regionList;
}
?>
