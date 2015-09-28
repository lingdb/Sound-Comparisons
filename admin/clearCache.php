<?php
require_once('common.php');
if(!session_validate($dbConnection))
  header('LOCATION: index.php');
if(!session_mayEdit($dbConnection))
  header('LOCATION: index.php');
require_once('../query/cacheProvider.php');
CacheProvider::cleanCache('../');
?>
<!DOCTYPE HTML>
<html>
  <?php
    $title = 'Cleared server side cache.';
    $jsFiles = array();
    require_once('head.php');
  ?>
  <body>
    <?php require_once('topmenu.php'); ?>
    <div class="row-fluid">
      <div class="span12">
        <h3>Server side cache cleared.</h3>
      </div>
  </body>
</html>
