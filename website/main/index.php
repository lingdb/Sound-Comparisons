<?php
  $startTime = microtime(true);
  /* Constants: */
  define('FLAGS_ENABLED', false);
  /* Requirements: */
  require_once 'config.php';
  require_once 'valueManager/RedirectingValueManager.php';
  /* Startup: */
  $dbConnection = Config::getConnection();
  $valueManager = RedirectingValuemanager::getInstance();
?><!DOCTYPE HTML><html><?php
    require 'head.php';
  ?><body><?php
      require_once 'menu/TopMenu.php';
    ?><div class="container-fluid"><?php
        $hideleft  = ' title="'.$valueManager->getTranslator()->st('hidelink_left').'"';
        $hideright = ' title="'.$valueManager->getTranslator()->st('hidelink_right').'"';
      ?><a class="hidelink btn" data-name="hidelink_left" data-target="#leftMenu"<?php echo $hideleft;?>>
        <i class="icon-chevron-left"></i>
      </a>
      <a class="hidelink btn" style="right: 5px;" data-name="hidelink_right" data-target="#rightMenu"<?php echo $hideright;?>>
        <i class="icon-chevron-right"></i>
      </a>
      <div class="mycontent myflow row-fluid"><?php
        require_once 'menu/LanguageMenu.php';
        require_once 'content.php';
        require_once 'menu/WordMenu.php';
      ?></div>
    </div>
    <div id='saveLocation' <?php
      echo $valueManager->link();
    ?> ></div>
    <div id='ipaKeyboard'>
      <div id='keyboardHead'>
        <button id='ipaButtonConsonants' class='ipaButtonAct'>Consonants Main</button>
        <button id='ipaButtonOthers' class='ipaButtonInAct'>Consonants Other</button>
        <button id='ipaButtonVowels' class='ipaButtonInAct'>Vowels</button>
        <button id='ipaButtonTone' class='ipaButtonInAct'>Tone</button>
        <i id="ipaClose" class="icon-remove-sign" title="Close this thing."></i>
      </div>
      <div id='ipaContent'>
        <?php require_once 'ipaKeyboard.php'; ?>
      </div>
      <div id='keyboardFooter'></div>
    </div>
  </body>
</html><?php
  $endTime = microtime(true);
  echo "<!-- Page generated in ".round(($endTime - $startTime), 4)."s -->";
  echo "<!-- ".$valueManager->show(false)." -->";
?>
