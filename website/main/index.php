<?php
  $startTime = microtime(true);
  /* Constants: */
  define('FLAGS_ENABLED', false);
  /* Requirements: */
  require_once 'config.php';
  require_once 'stopwatch.php';
  require_once 'valueManager/RedirectingValueManager.php';
  /* Startup: */
  Stopwatch::start('index.php');
  $dbConnection = Config::getConnection();
  require_once 'shortlink.php';
  $valueManager = RedirectingValuemanager::getInstance();
  $index = array(
    'hidelinkLeft'  => $valueManager->getTranslator()->st('hidelink_left')
  , 'hidelinkRight' => $valueManager->getTranslator()->st('hidelink_right')
  );
  //Building the head:
  require_once 'head.php';
  $index['head'] = $head;
  unset($head);
  //Building the TopMenu:
  require_once 'menu/TopMenu.php';
  $index['TopMenu'] = $topmenu;
  unset($topmenu);
  //Building the LanguageMenu:
  require_once 'menu/LanguageMenu.php';
  $index['LanguageMenu'] = $languageMenu;
  unset($languageMenu);
  //Building the content:
  require_once 'content.php';
  $index['content'] = $content;
  unset($content);
  //Building the WordMenu:
  require_once 'menu/WordMenu.php';
  $index['WordMenu'] = $wordMenu;
  unset($wordMenu);
  //Building the keyboard:
  require_once 'ipaKeyboard.php';
  $index['ipaKeyboard'] = $ipa;
  unset($ipa);
  //The saveLocation:
  $index['saveLocation'] = $valueManager->link();
  //The stopwatch:
  $index['stopwatch'] = Stopwatch::stats();
  //Processing the Content-type:
  $headers = getallheaders();
  $cType = $headers['Accept'];
  switch($cType){
    case (preg_match('/application\/json/i', $cType) ? true : false):
      header('Content-type: application/json');
      echo json_encode($index);
    break;
    case (preg_match('/text\/html/i', $cType) ? true : false):
    default:
      //Rendering:
      echo Config::getMustache()->render('index', $index);
      //Done :)
      $endTime = microtime(true);
      echo "<!-- Page generated in ".round(($endTime - $startTime), 4)."s -->";
      echo "<!-- ".$valueManager->show(false)." -->";
  }
?>
