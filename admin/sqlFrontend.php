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
    $title = 'Perform database operations via SQL';
    $jsFiles = array();
    require_once 'head.php';
  ?>
  <body>
    <?php require_once 'topmenu.php'; ?>
    <div class="alert alert-error">
      <h2>This feature is currently under construction.</h2>
      You cannot perform SQL operations now, but it will become possible in the future.
    </div>
    <form class="form-horizontal" action="query/sql.php?action=import" method="POST" enctype="multipart/form-data">
      <legend>Upload a .sql file:</legend>
      <div class="control-group">
        <label class="control-label" for="import">File to upload:</label>
        <div class="controls">
          <input name="import" type="file" required/>
        </div>
      </div>
      <div class="control-group">
        <div class="controls">
          <button type="submit" class="btn">Upload</button>
        </div>
      </div>
    </form>
    <form class="form-horizontal">
      <legend>Export a database dump:</legend>
      <div class="control-group">
        <label class="control-label" for="export">Download .sql file:</label>
        <div class="controls">
          <a class="btn" href="query/sql.php?action=export">Export</a>
        </div>
      </div>
    </form>
  </body>
</html>
