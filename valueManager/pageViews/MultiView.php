<?php
  require_once 'PageView.php';

  class MultiView extends PageView{
    public function getType(){return 'multiWordView';}

    public function displayName(){
      return $this->valueManager->getTranslator()->st('topmenu_views_multiview');
    }

    public function init(){
      $v = $this->valueManager;
      if($v->isUserCleaned()) return;
      if((count($v->getWords()) == 0) && (count($v->getLanguages()) == 0)){
        //Setting words:
        $words = $v->gwm()->getDefaults(true);
        $v->unsafeSetWords($words);
        //Setting Languages:
        $languages = $v->glm()->getDefaults(true);
        $v->unsafeSetLanguages($languages);
      }
    }
  }
?>
