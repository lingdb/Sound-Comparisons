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
    $title = 'Perform database operations via SQL';
    $jsFiles = array('dbimport.js');
    require_once('head.php');
  ?>
  <body>
    <?php require_once('topmenu.php'); ?>
    <div class="row-fluid" style="margin-left:20px">
      <div class="span6">
        <form id="fileform" class="form-horizontal" action="query/sql.php?action=import" 
          method="POST" enctype="multipart/form-data" target="iframe_post_form">
          <legend>Upload and Run SQL file(s):</legend>
          <div class="control-group">
            <label class="control-label" for="import[]">SQL File(s) to upload:</label>
            <div class="controls">
              <input name="import[]" type="file" required multiple/>
            </div>
          </div>
          <div class="control-group">
            <div class="controls">
              <button type="submit" class="btn" id="button_upload">Upload &amp; Run</button>
            </div>
          </div>
        </form>
      </div>
    </div>
    <iframe name="iframe_post_form" id="iframe_post_form" style="border:none;"></iframe>
  </body>
</html>
