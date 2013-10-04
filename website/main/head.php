<head>
  <title>
    <?php echo $valueManager->getTranslator()->getPageTitle($valueManager->getStudy()); ?>
  </title>
  <meta charset="utf-8">
  <?php if($valueManager->gpv()->isView('MapView')){ ?>
  <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
  <?php
  }
  require 'head/css.php';
  require 'head/js.php';
  ?>
</head>
<?php flush(); ?>
