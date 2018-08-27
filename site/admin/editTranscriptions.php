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
    $title = 'Edit Transcriptions';
    $jsFiles = array('extern/jquery.dataTables.js');
    require_once('head.php');
  ?>
  <body>
    <?php require_once('topmenu.php'); ?>
    <h3>&nbsp;&nbsp;Choose a study:</h3>
    <div class="btn-group">
    <?php
      $current = isset($_GET['study']) ? $_GET['study'] : '';
      foreach(DataProvider::getStudies() as $s){
        $style = ($s === $current) ? ' btn-inverse' : '';
        echo "<a class=\"btn$style\" href=\"?study=$s\">$s</a>";
      }
    ?>
    </div>
    <div style="margin:30px;">
      <table class="display table table-bordered" style="width:90%">
      <?php
      $head = '<tr>'
                .'<th>Phonetic</th>'
                .'<th>Word</th>'
                .'<th>Short Name</th>'
                .'<th>LgIx/FilePathPart</th>'
             .'</tr>';
      echo "<thead>$head</thead>";
      DataProvider::transcriptionTable($_GET['study']);
      foreach(DataProvider::$transcriptionTable as $t){
        echo "<tr>";
        echo "<td>".$t['Phonetic']."</td>";
        echo "<td>".$t['Word']."</td>";
        echo "<td>".$t['ShortName']."</td>";
        echo "<td>".$t['LgIxFPP']."</td>";
        echo "</tr>";
      }
      ?>
      </table>
      <script type="application/javascript">
        $(document).ready(function(){
          // $('table.display').DataTable({paging: true, ordering: true, order: [[ 2, "asc" ]]});
          var table = $('table.display').DataTable({paging: true, ordering: false});
          $('table.display thead th').each( function () {
            var title = $(this).text();
            $(this).html($(this).html()+'<br /><input type="text" placeholder="Search..." />' );
          } );
          table.columns().every( function () {
            var that = this;
            $( 'input', this.header() ).on( 'keyup change', function () {
              if ( that.search() !== this.value ) {
                that.search( this.value, true, false, true ).draw();
              }
            } );
          } );
        });
      </script>
    </div>
    <iframe name="iframe_post_form" id="iframe_post_form" style="border:none;"></iframe>
  </body>
</html>
