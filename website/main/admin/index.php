<?php
  require_once 'common.php';
  //The loginprocedure:
  if(!isset($_GET['action'])){
    $_GET['action'] = '';
  }
  switch($_GET['action']){
    case 'logout':
      session_destroy();
      header('LOCATION: index.php');
    break;
    case 'login':
      $user = strip_tags(stripslashes(mysql_real_escape_string($_POST['username'])));
      $pass = md5($_POST['password']);
      $query = 'SELECT UserId FROM Edit_Users WHERE Login = \''.$user.'\' AND Hash = \''.$pass.'\'';
      $set = mysql_query($query, $dbConnection);
      if($row = mysql_fetch_assoc($set)){
        $_SESSION['UserId'] = $row['UserId'];
        session_mkValid($user, $pass);
        header('LOCATION: index.php');
      }else{ ?>
       <!DOCTYPE HTML>
        <html><?php
          $title    = "Login failed, try again.";
          require 'head.php';
        ?><body><?php
          $loginMessage = "Login failed.";
          require 'loginForm.php';
        ?></body>
        </html>
      <?php }
    break;
    case 'updatePassword':
      if(session_validate($dbConnection)){
        $newP    = md5($_POST['new']);
        $confirm = md5($_POST['confirm']);
        if($newP != $confirm)
          die("New password doesn't match confirmation.");
        $uid = session_getUid();
        $q = "UPDATE Edit_Users SET Hash = '$newP' WHERE UserId = $uid";
        mysql_query($q, $dbConnection);
        session_destroy();
        header('LOCATION: index.php');
      }else die('Invalid session!');
    break;
    default:
      if(session_validate($dbConnection)){?>
       <!DOCTYPE HTML>
        <html><?php
          $title    = "Welcome to the administration area.";
          $jsFiles  = array("overview.js");
          require 'head.php';
        ?><body><?php
          require 'topmenu.php';
          require 'overview.php';
        ?></body>
        </html>
      <?php }else{?>
       <!DOCTYPE HTML>
        <html><?php
          $title    = "Login to perform administration tasks.";
          require 'head.php';
        ?><body><?php
          unset($loginMessage);
          require 'loginForm.php';
        ?></body>
        </html>
<?php }} /*Closing if and case blocks*/ ?>
