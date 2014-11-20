<?php
//FIXME REMOVE DISABLE PART BELOW
error_log('This feature is currently disabled.');
die('This feature is currently disabled.');
//FIXME REMOVE DISABLE PART ABOVE
chdir('..');
require 'common.php';
if(php_sapi_name() === 'cli'){
  //Translating $argv to $_GET,$_POST:
  if(count($argv) > 1){
    $action = $argv[1];
    switch($action){
      case 'import':
        if(count($argv) <= 2){
          die('Usage: php -f '.$argv[0]." import <file>\n");
        }
        $file = file_get_contents($argv[2]);
      break;
      case 'update':
      //FIXME IMPLEMENT!
      break;
    }
  }
}else{
  session_validate() or Config::error('403 Forbidden');
  session_mayEdit()  or Config::error('403 Forbidden');
  if(array_key_exists('action', $_GET)){
    $action = $_GET['action'];
    switch($action){
      case 'import':
        if(array_key_exists('import', $_FILES)){
          die(var_dump($_FILES));
          $file = file_get_contents($_FILES['import']['tmp_name']);
        }else{
          die('import file missing.');
        }
      break;
      case 'update':
        die('This option is only supported from cli.');
      break;
    }
  }else{
    die('action parameter missing.');
  }
}
//Performing $action:
switch($action){
  case 'export':
    $tables = array();
    $set = $dbConnection->query('SHOW TABLES');
    if($set !== false){
      while($row = $set->fetch_row()){
        array_push($tables, current($row));
      }
    }
    $qs = array(
      'SET AUTOCOMMIT=0;'
    , 'SET FOREIGN_KEY_CHECKS=0;'
    );
    foreach($tables as $t){
      $set = $dbConnection->query("SELECT * FROM $t");
      if($set !== false){
        $rows = array();
        while($row = $set->fetch_row()){
          for($i = 0; $i < count($row); $i++){
            $row[$i] = "'".$dbConnection->escape_string($row[$i])."'";
          }
          array_push($rows, '('.implode(', ',$row).')');
        }
        if(count($rows) > 0){
          array_push($qs
          , "DELETE FROM $t;"
          , "INSERT INTO $t VALUES ".implode(', ',$rows).';');
        }
      }
    }
    array_push($qs
    , 'SET FOREIGN_KEY_CHECKS=1;'
    , 'COMMIT;'
    , 'SET AUTOCOMMIT=1;'
    );
    if(php_sapi_name() !== 'cli'){
      header("Pragma: public");
      header("Expires: 0");
      header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
      header("Content-Type: application/octet-stream; charset=utf-8");
      header("Content-Disposition: attachment;filename=\"dump.sql\"");
      header("Content-Transfer-Encoding: binary");
    }
    echo implode("\n",$qs)."\n";
  break;
  case 'import':
    $dbConnection->multi_query($file);
  break;
  case 'update':
  break;
  default:
    echo "Unsupported action: $action\n";
}
?>
