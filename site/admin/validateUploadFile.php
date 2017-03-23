<?php
require_once('common.php');
if(!isset($_POST['action'])){
  echo "Ask the administrator...";
  return;
}

if(!session_validate($dbConnection))
  header('LOCATION: index.php');
if(!session_mayEdit($dbConnection))
  header('LOCATION: index.php');

if(isset($_POST['action']) && !empty($_POST['action'])) {
  switch($_POST['action']){
    case 'validateUploadFile':
      $fpp = $_POST['item'];
      $info = DataProvider::getInfoForFilePartPath($fpp);
      if(count($info) == 0) {
        $info['Study'] = "unknown";
        $info['ShortName'] = "unknown";
        $info['FilePartPath'] = $fpp;
      }
      echo json_encode($info);
      return;
      break;
  }
}
?>