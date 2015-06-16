<?php
chdir('..');
require 'common.php';
require_once '../query/cacheProvider.php';
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
    }
  }
}else{
  $allowed = session_validate() && session_mayEdit();
  if(!$allowed){//Special case for action=export:
    if(array_key_exists('ch1', $_GET) && array_key_exists('ch2', $_GET)){
      $db = Config::getConnection();
      $login = $dbConnection->escape_string($_GET['ch1']);
      $hash  = $dbConnection->escape_string($_GET['ch2']);
      $q = "SELECT AccessEdit FROM Edit_Users"
         . " WHERE Login = '$login' AND Hash = '$hash'";
      if($r = $db->query($q)->fetch_row()){
        $allowed = $r[0] == 1;
      }
      unset($db, $login, $hash, $q, $r);
    }
    if(!$allowed){
      Config::error('403 Forbidden');
      die('403 Forbidden');
    }
  }
  if(array_key_exists('action', $_GET)){
    $action = $_GET['action'];
    switch($action){
      case 'import':
        if(array_key_exists('import', $_FILES)){
          $file = file_get_contents($_FILES['import']['tmp_name']);
          error_log('Got some data:');
          error_log($file);
        }else{
          die('import file missing.');
        }
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
    CacheProvider::cleanCache('../');
    //Executing multiple queries:
    $report = array(); $i = 1;
    $worked = $dbConnection->multi_query($file);
    if(!$worked){
      array_push($report, $i);
    }
    while($dbConnection->more_results()){
      $worked = $dbConnection->next_result();
      $i++;
      if(!$worked){
        array_push($report, $i);
      }
    }
    ?><!DOCTYPE HTML>
    <html><?php
      $title = "SQL file processed.";
      require 'head.php';
    ?><body>
      <h3>File processed.</h3>
      <a href="..">Go back!</a>
        <?php if(count($report) > 0){ ?>
        <div class="well">
          There have been errors with the following query numbers:
          <code><?php echo implode(' ,', $report); ?></code>
        </div>
        <?php } ?>
      </body>
    </html><?php
  break;
  default:
    echo "Unsupported action: $action\n";
}
?>
