<?php
  require_once '../config.php';
  $config->overwriteLogin(array(
    'server' => null
  , 'user'   => null
  , 'passwd' => null
  , 'db'     => null
  ));
  $dbConnection = $config->getConnection();
  require_once 'validate.php';
  session_start();
?>
