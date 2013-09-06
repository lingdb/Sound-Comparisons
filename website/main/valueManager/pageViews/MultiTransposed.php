<?php
  require_once 'MultiView.php';

  class MultiTransposed extends MultiView{
    public function getType(){return 'multiViewTransposed';}
    public function displayName(){
      return $this->valueManager->getTranslator()->st('topmenu_views_multitransposed');
    }
  }
?>
