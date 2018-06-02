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
    $title = 'Export Study Data';
    $jsFiles = array('dbimport.js');
    require_once('head.php');
  ?>
  <body>
    <?php require_once('topmenu.php'); ?>
    <div class="row-fluid" style="margin-left:20px">
      <div class="span8">
        <form id="fileform" class="form-horizontal" action="query/sql.php?action=export01" 
          method="POST" target="iframe_post_form">
              <legend>Export Study Data</legend>
              <p>Please choose a study to download the TSV file:</p>
              <div class="btn-group">
              <?php
                // I do not why the first button doesn't work if one deletes the following line @TODO
                echo "<button type='submit' class='btn' id='button_upload' name='study' value='' style='display:none'></button>";
                foreach(DataProvider::getStudies() as $s){
                  echo "<button type='submit' class='btn' id='button_upload' name='study' value='$s'>$s</button>";
                }
              ?>
              </div>
        </form>
      </div>
    </div>
    <iframe name="iframe_post_form" id="iframe_post_form" style="border:none;"></iframe>
  </body>
</html>
