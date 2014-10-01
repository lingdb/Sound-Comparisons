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
      $user  = strip_tags(stripslashes($dbConnection->escape_string($_POST['username'])));
      $query = "SELECT UserId, Hash FROM Edit_Users WHERE Login = '$user'";
      $valid = false; $hash = '';
      $set   = $dbConnection->query($query);
      if($row = $set->fetch_assoc()){
        if(password_verify($_POST['password'], $row['Hash'])){
          $hash = $row['Hash'];
          $valid = true;
        }else{//Probably still an md5 hash - need to update.
          $hash = md5($_POST['password']);
          if($row['Hash'] === $hash){
            $valid = true;
            $bcHash = password_hash($_POST['password'], PASSWORD_BCRYPT);
            if($bcHash){
              $hash = $bcHash;
              $q = "UPDATE Edit_Users SET Hash = '$hash' WHERE UserId = ".$row['UserId'];
              $dbConnection->query($q);
            }
          }
        }
      }
      if($valid === true){
        $_SESSION['UserId'] = $row['UserId'];
        session_mkValid($user, $hash);
        header('LOCATION: index.php');
      }else{?>
        <!DOCTYPE HTML>
        <html><?php
          $title = "Login failed, try again.";
          require 'head.php';
        ?><body><?php
          $loginMessage = "Login failed.";
          require 'loginForm.php';
        ?></body>
        </html>
      <?php }
    break;
    case 'updatePassword':
      if(session_validate()){
        $newP    = $_POST['new'];
        $confirm = $_POST['confirm'];
        if($newP !== $confirm)
          Config::error("New password doesn't match confirmation.");
        $hash = password_hash($_POST['new'], PASSWORD_BCRYPT);
        if(!$hash){//Fallback to md5
          $hash = md5($_POST['new']);
        }
        $uid = session_getUid();
        $q   = "UPDATE Edit_Users SET Hash = '$hash' WHERE UserId = $uid";
        $dbConnection->query($q);
        session_destroy();
        header('LOCATION: index.php');
      }else Config::error('Invalid session!');
    break;
    default:
      if(session_validate()){
        if(session_mayEdit()){
          header('LOCATION: userAccount.php');
        }else{
          header('LOCATION: translate.php');
        }
      }else{?>
       <!DOCTYPE HTML>
        <html><?php
          $title = "Login to perform administration tasks.";
          require 'head.php';
        ?><body><?php
          unset($loginMessage);
          require 'loginForm.php';
        ?></body>
        </html>
<?php }} /*Closing if and case blocks*/ ?>
