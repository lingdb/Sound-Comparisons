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
    $title = 'Export Database Dump';
    $jsFiles = array();
    require_once('head.php');
  ?>
  <body>
    <?php require_once('topmenu.php'); ?>
    <div class="row-fluid" style="margin-left:20px">
      <div class="span6">
        <form class="form-horizontal">
          <legend>Export Database Data (SQL format):</legend>
          <div class="control-group">
           <p><i>Note:</i> The generated SQL file <b>only</b> contains the data of each defined table but <b>not</b> the CREATE TABLE, CREATE PROCEDURE, etc. statements!</p>
            <label class="control-label" for="export">Download SQL file:</label>
            <div class="controls">
              <a class="btn" href="query/sql.php?action=export">Export</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </body>
</html>
