<head>
  <title><?php
    echo $valueManager->getTranslator()->getPageTitle($valueManager->getStudy());
  ?></title>
  <meta charset="utf-8">
  <link href="css/extern/bootstrap.css" rel="stylesheet">
  <link href="css/myflow.css" rel="stylesheet">
  <link href="css/main.css" rel="stylesheet">
  <!-- Get rid of style below
  -->
  <link href="style.css" rel="stylesheet">
  <script src="js/extern/jquery.min.js"></script>
  <script src="js/extern/bootstrap.js"></script>
  <script src="js/extern/underscore-min.js"></script>
  <script src="js/extern/backbone-min.js"></script>
  <!--Check these:-->
  <script src="js/extern/jquery.cookie.js"></script>
  <script src="js/extern/jquery.scrollTo-1.4.2-min.js"></script>
  <script src="js/extern/jquery.mousewheel.min.js"></script>
  <script src="js/extern/jquery.json-2.3.min.js"></script>
  <?php
    // Google maps
    if($valueManager->gpv()->isView('MapView')){
      echo '<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />'
         . '<script type="text/javascript" '
         . 'src="http://maps.googleapis.com/maps/api/js?sensor=false&libraries=geometry"></script>'
         . '<script src="js/models/WordOverlay.js"></script>'
         . '<script src="js/models/Map.js"></script>'
         . '<script src="js/views/WordOverlayView.js"></script>'
         . '<script src="js/views/MapView.js"></script>';
    }
  ?>
  <!--     -->
  <script src="js/AudioLogic.js"></script>
  <script src="js/PlaySequence.js"></script>
  <script src="js/wordlistfilter.js"></script>
  <script src="js/load.js"></script>
  <script src="js/logging.js"></script>
  <script src="js/scroll.js"></script>
  <script src="js/singleLanguageView.js"></script>
  <script src="js/ipakeyboard.js"></script>
  <script src="js/models/StudyWatcher.js"></script>
  <script src="js/models/ViewWatcher.js"></script>
  <script src="js/models/SoundPlayOption.js"></script>
  <script src="js/views/HideLinks.js"></script>
  <script src="js/views/SoundPlayOptionView.js"></script>
  <script src="js/App.js"></script>
  <script src="js/main.js"></script>
</head>
<?php flush(); ?>
