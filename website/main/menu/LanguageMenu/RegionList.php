<?php
/**
  @param $regions Region[]
  @param $v ValueManager
  @param $t Translator
  @param [$showFlags = false]
  @param [$dl = false]
  @return regionList String Html
  Builds the RegionList for LanguageMenu.php
*/
function LanguageMenuBuildRegionList($regions, $v, $t, $showFlags = false, $dl = false){
  $regionList = $dl ? '<dl class="regionList">' : '<ul class="regionList">';
  foreach($regions as $r){
    $hasRegion = $v->hasRegion($r);
    //The regions title:
    $isMultiView = ($v->gpv()->isSelection() || $v->gpv()->isMapView());
    $isMapView   = $v->gpv()->isMapView();
    $rName       = $r->getShortName();
    $rTtip       = $r->getName();
    $languages   = $r->getLanguages($v->gpv()->isLanguageView());
    if(count($languages) === 0)
      continue;
    $checkbox = '';
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
      $checkbox = "<a $href title='$ttip'><i class='$icon'></i></a>";
    }
    //The Region item itself:
    $href = $hasRegion ? $v->delRegion($r)->link()
                       : $v->addRegion($r)->link();
    $triangle = $hasRegion ? 'icon-chevron-up rotate90' : 'icon-chevron-down';
    $title = "$checkbox<a class='color-region' $href title='$rTtip'><i class='$triangle'></i>$rName</a>";
    $regionList .= $dl ? "<dt>$title</dt>" : "<li>$title";
    //Content inside the region:
    if(!$hasRegion){
      $regionList .= $dl ? '<dd class="languageList">' : '<ul class="languageList">';
      foreach($languages as $l){
        $flag    = $showFlags ? $l->getFlag() : '';
        $sn      = $l->getShortName();
        $ln      = $l->getLongName(false);
        $hasL    = $v->hasLanguage($l);
        $checked = $hasL ? 'icon-check' : 'icon-chkbox-custom';
        if($hasL){
          if($isMultiView){ // Has && Multi
            if($isMapView){
              $ttip = "$ln\n".$t->st('multimenu_tooltip_del_map');
            }else{
              $ttip = "$ln\n".$t->st('multimenu_tooltip_del');
            }
            $href = $v->delLanguage($l)->setUserCleaned()->link('','data-href');
            $regionList .= "<li><a class='color-language' title='$ttip' $href>"
                         . "<i class='$checked'></i>$flag$sn</a></li>";
          }else{ // Has && ¬Multi
            $regionList .= "<li class='color-language selected' title='$ln'>$flag$sn</li>";
          }
        }else{
          if($isMultiView){ // ¬Has && Multi
            if($isMapView){
              $ttip = "$ln\n".$t->st('multimenu_tooltip_add_map');
            }else{
              $ttip = "$ln\n".$t->st('multimenu_tooltip_add');
            }
            $href = $v->addLanguage($l)->link('','data-href');
            $regionList .= "<li><a class='color-language' title='$ttip' $href>"
                         . "<i class='$checked'></i>$flag$sn</a></li>";
          }else{ // ¬Has && ¬Multi
            $goL = $v->gpv()->setLanguageView()->setLanguage($l)->link();
            $regionList .= "<li><a class='color-language' title='$ln' $goL>$flag$sn</a></li>";
          }
        }
      }
      $regionList .= $dl ? '</dd>' : '</ul>';
    }
    if(!$dl) $regionList .= '</li>';
  }
  if($dl)
    return $regionList.'</dl>';
  return $regionList.'</ul>';
}
?>
