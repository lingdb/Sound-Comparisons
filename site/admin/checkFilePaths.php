<?php
require_once('common.php');
if(!session_validate($dbConnection))
  header('LOCATION: index.php');
if(!session_mayEdit($dbConnection))
  header('LOCATION: index.php');
?>
<!DOCTYPE HTML>
<html>
  <?php
    $title = 'Check File Paths';
    $jsFiles = array('extern/jquery.dataTables.js');
    require_once('head.php');
  ?>
  <body>
    <?php require_once('topmenu.php'); ?>
    <h3>&nbsp;&nbsp;Choose a study to check file paths and number of transcriptions for:</h3>
    <div class="btn-group">
    <?php
      $current = isset($_GET['study']) ? $_GET['study'] : '';
      foreach(DataProvider::getStudies() as $s){
        $style = ($s === $current) ? ' btn-inverse' : '';
        echo "<a class=\"btn$style\" href=\"?study=$s\">$s</a>";
      }
    ?>
    </div>
    <?php if(isset($_GET['study'])){
      DataProvider::checkFilePaths($_GET['study']);
      echo "<br /><i><small>".DataProvider::$checkFilePathsFurtherCheckOfDisk."</small></i>";
    ?>
    <table class="display table table-bordered">
      <?php
        $head = '<tr>'
                  .'<th>Sound Path (disk)</th>'
                  .'<th>Sound Path (database)</th>'
                  .'<th>Short Name</th>'
                  .'<th>LanguageIx</th>'
                  .'<th>#Transcriptions ('.DataProvider::$checkFilePathsNumberOfWords.' words)</th>'
               .'</tr>';
        echo "<thead>$head</thead>";
        //Making sure we're at the right location:
        chdir('..');
        foreach(DataProvider::$checkFilePaths as $t){
          echo "<tr>";
          echo "<td>".$t['SoundPath']."</td>";
          echo "<td>".$t['FilePathPart']."</td>";
          echo "<td>".$t['ShortName']."</td>";
          echo "<td>{$t['LanguageIx']}</td>";
          echo "<td>{$t['NumOfTrans']}</td>";
          echo "</tr>";
        }
      ?>
    </table>
    <script type="application/javascript">
      $(document).ready(function(){
        $('table.display').DataTable({paging: false, ordering: true});
      });
    </script>
    <?php }?>
  </body>
</html>
