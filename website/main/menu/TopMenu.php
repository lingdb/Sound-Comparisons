<?php
  //Setup:
  $v = $valueManager;
  $t = $v->getTranslator();
  $topmenu = array();
  //Logo:
  $topmenu['logoTitle'] = $t->st('website_logo_hover');
  //Studies:
  $sid = $v->getStudy()->getId();
  $topmenu['currentStudyName'] = $v->getStudy()->getName($v);
  $studies = array();
  foreach(Study::getStudies() as $s){
    $entry = array(
      'currentStudy' => ($s->getId() === $sid)
    , 'studyName'    => $s->getName($v)
    );
    if(!$entry['currentStudy']){
      $entry['link'] = $v->gwo()->clear()->setRegions()->setLanguages()->setWords()->setStudy($s)->link();
    }
    array_push($studies, $entry);
  }
  $topmenu['studies'] = $studies;
  //PageView:
  $topmenu['pageViewTitle'] = $t->st('topmenu_views');
  $pv = $v->gpv();
  if($pv->isView('WordView')){
    $mapView  = $v->gpv()->setView('MapView')->setRegions()->setLanguages()->unsetUserCleaned()->link();
  }else
    $mapView    = $v->gpv()->setView('MapView')->setRegions()->setLanguages()->setWords()->unsetUserCleaned()->link();
  if($pv->isView('MapView')){
    $singleView = $v->gpv()->setView('WordView')->setLanguages()->unsetUserCleaned()->link();
  }else
    $singleView = $v->gpv()->setView('WordView')->setWords()->setLanguages()->unsetUserCleaned()->link();
  $languageView = $v->gpv()->setView('LanguageView')->setWords()->setLanguages()->unsetUserCleaned()->link();
  $multiView    = $v->gpv()->setView('MultiView')->setRegions()->setLanguages()->setWords()->unsetUserCleaned()->link();
  $multiViewT   = $v->gpv()->setView('MultiTransposed')->setRegions()->setLanguages()->setWords()->unsetUserCleaned()->link();
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
  require_once 'TopMenu/Color.php';
  $topmenu['pageViews'] = array(
    array(
      'active'  => $pv->isView('MapView')
    , 'link'    => $mapView
    , 'content' => tColor(COLOR_M, $names['m'])
    , 'title'   => $hovers['m']
    , 'img'     => 'maps.png'
    )
  , array(
      'active'  => $pv->isView('WordView')
    , 'link'    => $singleView
    , 'content' => tColor(COLOR_W, $names['w'])
    , 'title'   => $hovers['w']
    , 'img'     => '1w.png'
    )
  , array(
      'active'  => $pv->isView('LanguageView')
    , 'link'    => $languageView
    , 'content' => tColor(COLOR_L, $names['l'])
    , 'title'   => $hovers['l']
    , 'img'     => '1l.png'
    )
  , array(
      'active'  => $pv->isView('MultiView')
    , 'link'    => $multiView
    , 'content' => tColor(COLOR_LW, $names['lw'])
    , 'title'   => $hovers['lw']
    , 'img'     => 'lw.png'
    )
  , array(
      'active'  => $pv->isView('MultiTransposed')
    , 'link'    => $multiViewT
    , 'content' => tColor(COLOR_WL, $names['wl'])
    , 'title'   => $hovers['wl']
    , 'img'     => 'wl.png'
    )
  );
  //Downloads:
  $topmenu['csvLink']    = $v->link('export/csv');
  $topmenu['csvTitle']   = $t->st('topmenu_download_csv');
  $topmenu['sndLink']    = $v->link('export/soundfiles');
  $topmenu['sndTitle']   = $t->st('topmenu_download_zip');
  $topmenu['cogTitle']   = $t->st('topmenu_download_cogTitle');
  $topmenu['wordByWord'] = $t->st('topmenu_download_wordByWord');
  $topmenu['format']     = $t->st('topmenu_download_format');
  $topmenu['formats']    = array('mp3','ogg');
  //Languages:
  $topmenu['currentFlag'] = $t->showFlag();
  $otherTranslations = array();
  foreach($t->getOthers() as $ot){
    array_push($otherTranslations, array(
      'link' => $v->setTranslator($ot)->link()
    , 'flag' => $ot->showFlag()
    , 'name' => $ot->showName()
    ));
  }
  $topmenu['otherTranslations'] = $otherTranslations;
  //SoundPlayOptions:
  $topmenu['soundClickTitle'] = $t->st('topmenu_soundoptions_tooltip');
  $topmenu['soundHoverTitle'] = $t->st('topmenu_soundoptions_hover');
  //About:
  $topmenu['aboutEntries'] = array(
    array(
      'link'  => $v->gpv()->setView('WhoAreWeView')->link()
    , 'about' => $t->st('topmenu_about_whoarewe')
    )
  , array(
      'link'  => 'href="'.$t->st('topmenu_about_furtherinfo_href').'"'
    , 'about' => $t->st('topmenu_about_furtherinfo')
    )
  , array(
      'link'  => 'href="'.$t->st('topmenu_about_research_href').'"'
    , 'about' => $t->st('topmenu_about_research')
    )
  , array(
      'link'  => 'href="'.$t->st('topmenu_about_contact_href').'"'
    , 'about' => $t->st('topmenu_about_contact')
    )
  );
  //Cleanup:
  unset($sid, $studies, $pv, $hovers, $names, $mapView, $singleView, $languageView, $multiView, $multiViewT, $otherTranslations);
?>
