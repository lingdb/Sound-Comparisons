<?php /* External .jsn files */ ?>
  <script src="js/extern/jquery.min.js"></script>
  <script src="js/extern/bootstrap.js"></script>
  <script src="js/extern/underscore-min.js"></script>
  <script src="js/extern/backbone-min.js"></script>
<?php /* Check if these are still necessary: */ ?>
  <script src="js/extern/jquery.cookie.js"></script>
  <script src="js/extern/jquery.scrollTo-1.4.2-min.js"></script>
  <script src="js/extern/jquery.mousewheel.min.js"></script>
  <script src="js/extern/jquery.json-2.3.min.js"></script>
<?php if($valueManager->gpv()->isView('MapView')){ ?>
  <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false&libraries=geometry"></script>
<?php } if(CONFIG::$debug){ ?>
  <script src="js/AudioLogic.js"></script>
  <script src="js/PlaySequence.js"></script>
  <script src="js/wordlistfilter.js"></script>
  <script src="js/load.js"></script>
  <script src="js/logging.js"></script>
  <script src="js/scroll.js"></script>
  <script src="js/singleLanguageView.js"></script>
  <script src="js/ipakeyboard.js"></script>
  <script src="js/models/Map.js"></script>
  <script src="js/models/SoundPlayOption.js"></script>
  <script src="js/models/StudyWatcher.js"></script>
  <script src="js/models/ViewWatcher.js"></script>
  <script src="js/models/WordOverlay.js"></script>
  <script src="js/views/HideLinks.js"></script>
  <script src="js/views/MapView.js"></script>
  <script src="js/views/SoundControlView.js"></script>
  <script src="js/views/SoundPlayOptionView.js"></script>
  <script src="js/views/ColorCalcView.js"></script>
  <script src="js/views/WordOverlayView.js"></script>
  <script src="js/App.js"></script>
  <script src="js/main.js"></script>
<?php }else{ ?>
  <script src="js/min.js"></script>
<?php } ?>
