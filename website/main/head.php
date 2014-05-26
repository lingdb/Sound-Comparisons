<?php 
  $head = array(
    'title' => $valueManager->getTranslator()->getPageTitle($valueManager->getStudy())
  , 'isMapView' => $valueManager->gpv()->isView('MapView')
  );
?>
