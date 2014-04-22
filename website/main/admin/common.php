<?php
  require_once '../config.php';
  Config::setAdmin();
  $dbConnection = Config::getConnection();
  require_once 'validate.php';
  session_start();
?>
