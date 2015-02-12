<?php
require 'common.php';
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
    require_once 'head.php';
  ?>
  <body>
    <?php require_once 'topmenu.php'; ?>
    <table class="display table table-bordered">
      <?php
        $head = '<tr><th>Missing files:</th></tr>';
        echo "<thead>$head</thead>";
        //Making sure we're at the right location:
        chdir('..');
        //getTranscriptions fills the missingSounds:
        foreach(DataProvider::getStudies() as $s){
          DataProvider::getTranscriptions($s);
        }
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
  </body>
</html>
