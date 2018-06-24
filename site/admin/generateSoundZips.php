<?php
  require_once('common.php');
  /*Login check and procedure*/
  if(!session_validate($dbConnection))
    header('LOCATION: index.php');
  if(!session_mayEdit($dbConnection))
    header('LOCATION: index.php');
?>
<!DOCTYPE HTML>
<html>
  <?php
    $title = 'Generate Sound ZIPs';
    require_once('head.php');
  ?>
  <body>
    <?php require_once('topmenu.php'); ?>

    <h3>&nbsp;&nbsp;Choose a study to generate the Sound ZIP archive:</h3>

    <div class="row-fluid" style="margin-left:20px">
      <div class="span8">

        <div class="btn-group">
        <?php
          $current = isset($_GET['study']) ? $_GET['study'] : '';
          foreach(DataProvider::getStudies() as $s){
            $style = ($s === $current) ? ' btn-inverse' : '';
            echo "<a class=\"btn$style\" href=\"?study=$s\">$s</a>";
          }
        ?>
        </div>

      </div>
      <div class="span8" style="margin-top:20px">
      <?php if(isset($_GET['study'])){
        chdir('../sound');
        $studyName = $_GET['study'];
        $s = $dbConnection->escape_string($studyName);
        $q = "SELECT FilePathPart as P FROM Languages_$s";
        $set = DataProvider::fetchAll($q);
        if(count($set)>0){
          $output = null;
          $zipFile = $studyName.".zip";
          echo "Generating ZIP archive $zipFile - database lists ".count($set)." languages.<br><br>";
          exec("zip -rq ../offline/".$zipFile." ../offline/version 2>&1", $output);
          if(count($output)>0){
            var_dump($output);
          }
          $output = null;
          echo "<br>";
          foreach($set as $f){
            exec("zip -ur ../offline/".$zipFile." ".$f['P']."/*.mp3 ".$f['P']."/*.ogg 2>&1", $output);
            if(count($output) > 0){
              if(preg_match('/adding/', $output[0])){
                echo "<b>".$f['P']."</b><br>";
                echo "OK";
                echo "<br><br>";
                ob_flush();
                flush();
              }
            }
            $output = null;
          }
          $output = null;
          exec("zip -dq ../offline/".$zipFile." ../offline/version > /dev/null 2>&1", $output);
          if(count($output)>0){
            var_dump($output);
          }
        }
      }
      ?>
      </div>
    </div>

  </body>
</html>