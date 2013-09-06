<?php
  $startTime = microtime(true);
  /* Requirements: */
  require_once 'config.php';
  require_once 'valueManager/RedirectingValueManager.php';
  /* Startup: */
  $dbConnection = $config->getConnection();
  $valueManager = new RedirectingValueManager($dbConnection, $config);
?><!DOCTYPE HTML><html>
  <?php require 'head.php'; ?>
  <body>
    <div class="navbar row-fluid" id="topMenu">
      <div class="navbar-inner offset1 span10">
      <?
        include 'menu/TopMenu/Logo.php';
        include 'menu/TopMenu/Languages.php';
        include 'menu/TopMenu/About.php';
      ?>
      </div>
    </div>
  </body>
</html><?php
  $endTime = microtime(true);
  echo "<!-- Page generated in ".round(($endTime - $startTime), 4)."s -->";
  echo "<!-- ".$valueManager->show(false)." -->";
?>
