<?php
/**
  This script hands out informations regarding the static and dynamic translations of the site.
  The Idea is, to make these translations accessible for JavaScript,
  so that they can be cached client-side,
  and may be used to generate different presentations of the site.
*/
//Setup:
require_once 'translationProvider.php';
require_once '../config.php';
//Actual work:
Config::setResponseJSON();
//Defaulting our action:
if(!array_key_exists('action',$_GET)){
  $_GET['action'] = '';
}
//Acting depeding on action:
switch($_GET['action']){
  case 'dynamic':
    if(array_key_exists('translationId', $_GET)){
      echo Config::toJSON(TranslationProvider::getDynamic($_GET['translationId']));
    }else{
      Config::setResponse(400);
      echo Config::toJSON(array(
        'msg' => 'You need to specify a translationId for action=dynamic.'
      ));
    }
  break;
  case 'static':
    if(array_key_exists('translationId', $_GET)){
      echo Config::toJSON(TranslationProvider::getStatic($_GET['translationId']));
    }else{
      Config::setResponse(400);
      echo Config::toJSON(array(
        'msg' => 'You need to specify a translationId for action=static.'
      ));
    }
  break;
  case 'summary':
    echo Config::toJSON(TranslationProvider::getSummary());
  break;
  default:
    Config::setResponse(400);
    echo Config::toJSON(array(
      'msg'    => '"action" variable must be specified, '
                . 'carrying one of the action values.'
    , 'action' => array('summary', 'static', 'dynamic')
    ));
}
?>
