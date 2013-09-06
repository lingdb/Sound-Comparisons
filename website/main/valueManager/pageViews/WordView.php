<?php
  require_once 'PageView.php';

  class WordView extends PageView{

    public function getType(){return 'singleWordView';}

    public function displayName(){
      return $this->valueManager->getTranslator()->st('topmenu_views_wordview');
    }

    public function init(){
      $v = $this->valueManager;
      if($v->isUserCleaned()) return;
      if(count($v->getWords()) == 0){
        $words = $v->gwm()->getDefaults();
        $v->unsafeSetWords($words);
        $v->unsafeSetLanguages(array());
      }
    }
  }
?>
