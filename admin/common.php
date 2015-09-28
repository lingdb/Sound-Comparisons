<?php
  require_once('../config.php');
  require_once('../extern/password.php');
  Config::setAdmin();
  $dbConnection = Config::getConnection();
  require_once('validate.php');
  session_start();
