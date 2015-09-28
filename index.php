<?php
  $startTime = microtime(true);
  /* Constants: */
  define('FLAGS_ENABLED', false);
  /* Requirements: */
  require_once('config.php');
  require_once('query/translationProvider.php');
  /* Startup: */
  $dbConnection = Config::getConnection();
  $index = array(
    'hidelinkLeft'  => TranslationProvider::staticTranslate('hidelink_left')
  , 'hidelinkRight' => TranslationProvider::staticTranslate('hidelink_right')
  , 'head' => array(
      'title' => 'Site loading, please wait'
    , 'requirejs' => 'js/App'
    )
  );
  //Checking for minified js/App setup:
  require_once('Git.php');
  if(Git::getBranch() === 'master'){
    $app = 'js/App-minified';
    if(file_exists('./'.$app.'.js')){
      $index['head']['requirejs'] = $app;
    }
  }
  //Making sure we get our appSetup:
  $index['appSetup'] = true;
  //Processing the Content-type:
  $headers = getallheaders();
  if(!array_key_exists('Accept', $headers)){
    $headers['Accept'] = 'text/html';
  }
  $cType = $headers['Accept'];
  switch($cType){
    case (preg_match('/application\/json/i', $cType) ? true : false):
      header('Content-type: application/json');
      Config::toJSON($index);
    break;
    case (preg_match('/text\/html/i', $cType) ? true : false):
    default:
      //Rendering:
      echo Config::getMustache()->render('index', $index);
      //Done :)
      $endTime = microtime(true);
      echo "<!-- Page generated in ".round(($endTime - $startTime), 4)."s -->";
  }
