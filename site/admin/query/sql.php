<?php
chdir('..');
require_once('common.php');
require_once('../config.php');
require_once('../query/cacheProvider.php');
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
        $imports = $_FILES['import'];
        if(count($imports['name']) === 1 && $imports['name'][0] === ''){
          die('⁉️ Please choose at least one import file ...');
        }else{
          $files = [];
          while(count($imports['name']) > 0){
            array_push($files, array(
              'name' => array_pop($imports['name'])
            , 'path' => array_pop($imports['tmp_name'])
            ));
          }
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
  case 'genLgIdx':
    CacheProvider::cleanCache('../');
    $lgsIdx = array();
    $availableLgs = array();
    array_push($lgsIdx, "idx\tprefix");
    $aLSet = $dbConnection->query('SHOW TABLES LIKE "Languages_%"');
    if($aLSet !== false){
      while($row = $aLSet->fetch_row()){
        array_push($availableLgs, current($row));
      }
      foreach($availableLgs as $lg){
        $set = $dbConnection->query('select `LanguageIx`, `FilePathPart` from '.$lg);
        if($set !== false){
          while($row = $set->fetch_array(MYSQLI_NUM)){
            array_push($lgsIdx, $row[0]."\t".$row[1]);
          }
        } else {
          ?>‼️ An error occurred for <?php echo $lg ?>!<?php
          return;
        }
      }
      if(php_sapi_name() !== 'cli'){
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/octet-stream; charset=utf-8");
        header("Content-Disposition: attachment;filename=\"lg_idx_filename.txt\"");
        header("Content-Transfer-Encoding: binary");
      }
      echo implode("\n",$lgsIdx)."\n";
    } else {
      ?>‼️ No language tables found!<?php
    }
  break;
  case 'genPageDynTrans':
    CacheProvider::cleanCache('../');
    $template = array();
    $study = $_REQUEST['study'];
    $lgid = $_REQUEST['transLg'];
    array_push($template, '"TranslationId","Category","Field","Trans"');
    $aLSet = $dbConnection->query("SELECT 'WordsTranslationProvider-Words_-Trans_FullRfcModernLg01', concat('$study-',IxElicitation,IxMorphologicalInstance) AS c, REPLACE(FullRfcModernLg01,'\"','\"\"') AS t FROM Words_$study ORDER BY IxElicitation,IxMorphologicalInstance;");
    if($aLSet !== false){
      while($row = $aLSet->fetch_assoc()){
        array_push($template, "$lgid,\"".implode('","',$row).'"'); 
      }
      if(php_sapi_name() !== 'cli'){
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/octet-stream; charset=utf-8");
        header("Content-Disposition: attachment;filename=\"Page_DynamicTranslation$study.txt\"");
        header("Content-Transfer-Encoding: binary");
      }
      echo implode("\n",$template)."\n";
    } else {
      ?>‼️ No word tables found for <?php
      echo "'$study'!";
    }
  break;
  case 'import':
    CacheProvider::cleanCache('../');
    foreach($files as $f){
      //Executing multiple queries:
      $report = array(); $i = 1;
      $sql = file_get_contents($f['path']);
      $worked = $dbConnection->multi_query($sql);
      if(!$worked){
        array_push($report, 'File: '.$f['name'].' Query: '.$i);
        array_push($report, '<blockquote>'.$dbConnection->error.'</blockquote>');
      }
      while($dbConnection->more_results()){
        $worked = $dbConnection->next_result();
        $i++;
        if(!$worked){
          array_push($report, 'File: '.$f['name'].' Query: '.$i);
          array_push($report, '<blockquote>'.$dbConnection->error.'</blockquote>');
        }
      }
    }
    ?><!DOCTYPE HTML>
    <html>
    <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <link rel="Stylesheet" type="text/css" href="../../css/extern/bootstrap.css" media="screen" />
      <link rel="Stylesheet" type="text/css" href="../css/style.css" media="screen" />
      <link rel="Stylesheet" type="text/css" href="../css/extern/jquery.dataTables.css" media="screen" />
      <script type='application/javascript' src='../../js/bower_components/jquery/dist/jquery.min.js'></script>
      <script type='application/javascript' src='../../js/extern/bootstrap.js'></script>
      <script type='application/javascript' src='../../js/bower_components/underscore/underscore-min.js'></script>
      <script type='application/javascript' src='../../js/bower_components/backbone/backbone-min.js'></script>
    </head>
    <body style="margin:20px">
      <h3>File(s) processed.</h3>
        <?php if(count($report) > 0){ ?>
        <div class="well">
          There have been <font color="red">errors</font> with the following query numbers:
          <br /><br />
          <?php echo implode(' ,', $report); ?>
        </div>
        <?php } else { ?>  No errors 😀 <?php }?>
      </body>
    </html><?php
  break;
  case 'soundupload':
  CacheProvider::cleanCache('../');
  $count = 0;
  $report = array();
  if ($_SERVER['REQUEST_METHOD'] == 'POST'){
      foreach ($_FILES['files']['name'] as $i => $name) {
        echo $_FILES['files']['name'][$i]."<br>";
          // if (strlen($_FILES['files']['name'][$i]) > 1) {
          //     if (move_uploaded_file($_FILES['files']['tmp_name'][$i], $_SERVER['DOCUMENT_ROOT'].'/'.Config::$soundPath.'/test/'.$name)) {
          //         $count++;
          //     } else {
          //       array_push($report, 'File '.$name.' could not be uploaded');
          //     }
          // }
      }
      echo $count;
  }
  break;
  default:
    echo "Unsupported action: $action\n";
}
