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
    $title = 'Checks for database integrity';
    $jsFiles = array('extern/jquery.dataTables.js');
    require_once('head.php');
  ?>
  <body>
    <?php
      require_once('topmenu.php');
      require_once('query/Integrity.php');
      $data = Integrity::checkIntegrity();
      $mkTable = function($arr){
        $head = '<tr><th>Table:</th><th>Data:</th><th>Reason:</th></tr>';
        $ret = "<table class='display table table-bordered'><thead>$head</thead>";
        foreach($arr as $tName => $tData){
          foreach($tData as $entry){
            $data = $entry['json'];
            $reason = $entry['reason'];
            $ret .= "<tr><td>$tName</td><td>$data</td><td>$reason</td></tr>";
          }
        }
        $ret .= "<tfoot>$head</tfoot></table>";
        return $ret;
      };
      echo "<h2>Unexpected Data:</h2>";
      echo $mkTable($data['notValues']);
      echo "<h2>Invalid foreign keys:</h2>";
      echo $mkTable($data['fk']);
      echo "<h2>Invalid primary keys:</h2>";
      echo $mkTable($data['pk']);
    ?>
    <script type="application/javascript">
      $(document).ready(function(){
        $('table.display').DataTable();
      });
    </script>
  </body>
</html>
