<?php
/**
  This script shall act as a proxy for different parts of the site,
  that may be requested as JSON.
*/
//Setup:
require_once '../config.php';
require_once '../stopwatch.php';
require_once '../valueManager/RedirectingValueManager.php';
if(!array_key_exists('part', $_GET)){
  Config::error('The part is missing - I cannot work like this.');
}
$dbConnection = Config::getConnection();
$valueManager = RedirectingValuemanager::getInstance();
//Real work:
header('Content-type: application/json');
switch($_GET['part']){
  case 'TopMenu':
    require_once '../menu/TopMenu.php';
    echo json_encode($topmenu);
  break;
  case 'LanguageMenu':
    require_once '../menu/LanguageMenu.php';
    echo json_encode($languageMenu);
  break;
  case 'WordMenu':
    require_once '../menu/WordMenu.php';
    echo json_encode($wordMenu);
  break;
  case 'content':
    require_once '../content.php';
    echo json_encode($content);
  break;
}
?>
