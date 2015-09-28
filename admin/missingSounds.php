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
    $title = 'Sound files that could not be found';
    $jsFiles = array('extern/jquery.dataTables.js');
    require_once('head.php');
  ?>
  <body>
    <?php require_once('topmenu.php'); ?>
    <h3>Choose a study to list missing files for:</h3>
    <div class="btn-group">
    <?php
      $current = isset($_GET['study']) ? $_GET['study'] : '';
      foreach(DataProvider::getStudies() as $s){
        $style = ($s === $current) ? ' btn-inverse' : '';
        echo "<a class=\"btn$style\" href=\"?study=$s\">$s</a>";
      }
    ?>
    </div>
    <?php if(isset($_GET['study'])){ ?>
    <table class="display table table-bordered">
      <?php
        $head = '<tr><th>Missing files:</th></tr>';
        echo "<thead>$head</thead>";
        //Making sure we're at the right location:
        chdir('..');
        DataProvider::getTranscriptions($_GET['study']);
        //We use missingSounds to fill the table:
        foreach(DataProvider::$missingSounds as $sound){
          echo "<tr><td>$sound</td></tr>";
        }
        echo "<tfoot>$head</tfoot>";
      ?>
    </table>
    <script type="application/javascript">
      $(document).ready(function(){
        $('table.display').DataTable({paging: false, ordering: false});
      });
    </script>
    <?php } ?>
  </body>
</html>
