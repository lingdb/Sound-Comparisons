<?php
  require_once '../config.php';
  Config::overwriteLogin(array(
    'server' => null
  , 'user'   => null
  , 'passwd' => null
  , 'db'     => null
  ));
  $dbConnection = Config::getConnection();
  require_once 'validate.php';
  session_start();
?>
