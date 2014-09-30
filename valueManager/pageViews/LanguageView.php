<?php
  require_once 'PageView.php';

  class LanguageView extends PageView{

    public function getType(){return 'languageView';}

    public function displayName(){
      return $this->valueManager->getTranslator()->st('topmenu_views_languageview');
    }

    public function init(){
      $v = $this->valueManager;
      if($v->isUserCleaned()) return;
      if(count($v->getLanguages()) == 0){
        $languages = $v->glm()->getDefaults();
        $v->unsafeSetLanguages($languages);
        $v->unsafeSetWords(array());
      }
    }
  }
?>
