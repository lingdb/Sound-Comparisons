<?php
  require_once 'PageView.php';

  class MapView extends PageView{
    public function getType(){return 'mapView';}

    public function displayName(){
      return $this->valueManager->getTranslator()->st('topmenu_views_mapview');
    }

    public function init(){
      $v = $this->valueManager;
      $studyId = $v->getStudy()->getId();
      if(!$v->hasWord()){ //Find a default word
        $words = $v->gwm()->getDefaults();
        $v->unsafeSetWords($words);
      }
      if($v->isUserCleaned()) return;
      if(count($v->getLanguages()) == 0){ //Find default languages
        //Paul noted the mapView shall default to all languages:
        $languages = $v->getStudy()->getLanguages();
        $excludes  = $v->getStudy()->getMapExcludeLanguages();
        $v->unsafeSetLanguages(DBEntry::difference($languages, $excludes));
      }
    }

  }
?>
