<?php
/**
  This script hands out informations regarding the static and dynamic translations of the site.
  The Idea is, to make these translations accessible for JavaScript,
  so that they can be cached client-side,
  and may be used to generate different presentations of the site.
*/
//Setup:
chdir('..');
require_once 'config.php';
require_once 'valueManager/RedirectingValueManager.php';
$dbConnection = Config::getConnection();
$valueManager = RedirectingValuemanager::getInstance();
//Actual work:
Config::setResponseJSON();
switch($_GET['action']){
  case 'dynamic':
    if(array_key_exists('translationId', $_GET)){
      $tId = $dbConnection->escape_string($_GET['translationId']);
      $q   = "SELECT Category, Field, Trans FROM Page_DynamicTranslation WHERE TranslationId = $tId";
      $set = $dbConnection->query($q);
      $ret = array();
      while($r = $set->fetch_assoc()){
        array_push($ret, $r);
      }
      echo json_encode($ret);
    }else{
      Config::setResponse(400);
      echo json_encode(array(
        'msg' => 'You need to specify a translationId for action=dynamic.'
      ));
    }
  break;
  case 'static':
    if(array_key_exists('translationId', $_GET)){
      $tId = $dbConnection->escape_string($_GET['translationId']);
      $q   = "SELECT Req, Trans FROM Page_StaticTranslation WHERE TranslationId = $tId";
      $set = $dbConnection->query($q);
      $ret = array();
      while($r = $set->fetch_assoc()){
        $ret[$r['Req']] = $r['Trans'];
      }
      echo json_encode($ret);
    }else{
      Config::setResponse(400);
      echo json_encode(array(
        'msg' => 'You need to specify a translationId for action=static.'
      ));
    }
  break;
  case 'summary':
    $q = 'SELECT TranslationId, TranslationName, BrowserMatch, ImagePath, '
       . 'RfcLanguage, UNIX_TIMESTAMP(lastChangeStatic), UNIX_TIMESTAMP(lastChangeDynamic) '
       . 'FROM Page_Translations WHERE Active = 1';
    $set   = $dbConnection->query($q);
    $ret   = array();
    $trans = $valueManager->gtm()->getTarget();
    while($r = $set->fetch_assoc()){
      $ret[$r['TranslationId']] = array(
        'TranslationId'     => $r['TranslationId']
      , 'TranslationName'   => $r['TranslationName']
      , 'BrowserMatch'      => $r['BrowserMatch']
      , 'ImagePath'         => $r['ImagePath']
      , 'RfcLanguage'       => $r['RfcLanguage']
      , 'lastChangeStatic'  => $r['UNIX_TIMESTAMP(lastChangeStatic)']
      , 'lastChangeDynamic' => $r['UNIX_TIMESTAMP(lastChangeDynamic)']
      );
    }
    echo json_encode($ret);
  break;
  default:
    Config::setResponse(400);
    echo json_encode(array(
      'msg'    => '"action" variable must be specified, '
                . 'carrying one of the action values.'
    , 'action' => array('summary', 'static', 'dynamic')
    ));
}
?>
