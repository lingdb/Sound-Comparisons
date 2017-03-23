<?php
  chdir('..');
  require_once('common.php');
  /* Checking for edit rights: */
  if(!session_isSuperuser())
    Config::error('You are not allowed to access this feature.');
  /* Ensuring an action is given: */
  if(!isset($_GET['action']))
    Config::error('Missing get parameter:action!');
  /* Dealing with the action: */
  switch($_GET['action']){
    /* Parameters: username, password, mayTranslate, mayEdit */
    case 'create':
      $username     = $dbConnection->escape_string($_POST['username']);
      $password     = password_hash($_POST['password'], PASSWORD_BCRYPT);
      $mayTranslate = ($_POST['mayTranslate'] === '1') ? '1' : '0';
      $mayEdit      = ($_POST['mayEdit']      === '1') ? '1' : '0';
      $mayUpload    = ($_POST['mayUpload']    === '1') ? '1' : '0';
      $isSuperuser  = ($_POST['isSuperuser']  === '1') ? '1' : '0';
      if(!$password){//Fallback for md5:
        $password = md5($_POST['password']);
      }
      /* Checking that username is not taken: */
      $q = "SELECT COUNT(*) FROM Edit_Users WHERE Login = '$username'";
      $r = $dbConnection->query($q)->fetch_row();
      if($r[0] > 0){
        Config::error("Login '$username' already taken.");
        echo "Login '$username' already taken.";
      } else {
        /* Inserting new user: */
        $q = "INSERT INTO Edit_Users(Login, Hash, AccessEdit, AccessTranslate, AccessUpload, AccessSuperuser) "
           . "VALUES ('$username','$password',$mayEdit,$mayTranslate,$mayUpload,$isSuperuser)";
        $dbConnection->query($q);
        echo 'OK';
      }
    break;
    /* Parameters: userid, login, password, mayTranslate, mayEdit, mayUpload, isSuperuser */
    case 'update':
      if(!isset($_POST['userid'])) Config::error('userid missing!');
      $userid = $dbConnection->escape_string($_POST['userid']);
      if(isset($_POST['login'])){
        $login = $dbConnection->escape_string($_POST['login']);
        /* Ensuring no other user has the given login: */
        $q = "SELECT COUNT(*) FROM Edit_Users WHERE UserId != $userid AND Login = '$login'";
        $r = $dbConnection->query($q)->fetch_row();
        if($r[0] == 0){
          /* Updating the Login: */
          $q = "UPDATE Edit_Users SET Login = '$login' WHERE UserId = $userid";
          $dbConnection->query($q);
          echo "Updated login name.\n";
        }else{
          echo "Sorry, update failed! Please ask the administrator for user '".$login."'.";
        }
      }
      if(isset($_POST['password']))
        if($_POST['password'] != ''){
          $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
          if(!$password){//Fallback for md5:
            $password = md5($_POST['password']);
          }
          $q = "UPDATE Edit_Users SET Hash = '$password' WHERE UserId = $userid";
          $dbConnection->query($q);
          echo "Updated password.\n";
      }
      if(isset($_POST['mayTranslate'])){
        $mayT = $dbConnection->escape_string($_POST['mayTranslate']);
        if($mayT != '1') $mayT = '0';
        $q = "UPDATE Edit_Users SET AccessTranslate = $mayT WHERE UserId = $userid";
        $dbConnection->query($q);
        echo "Updated translation access.\n";
      }
      if(isset($_POST['mayEdit'])){
        $mayE = $dbConnection->escape_string($_POST['mayEdit']);
        if($mayE != '1') $mayE = '0';
        $q = "UPDATE Edit_Users SET AccessEdit= $mayE WHERE UserId = $userid";
        $dbConnection->query($q);
        echo "Updated edit access.\n";
      }
      if(isset($_POST['mayUpload'])){
        $mayU = $dbConnection->escape_string($_POST['mayUpload']);
        if($mayU != '1') $mayU = '0';
        $q = "UPDATE Edit_Users SET AccessUpload= $mayU WHERE UserId = $userid";
        $dbConnection->query($q);
        echo "Updated upload access.\n";
      }
      if(isset($_POST['isSuperuser'])){
        $mayS = $dbConnection->escape_string($_POST['isSuperuser']);
        if($mayS != '1') $mayS = '0';
        $q = "UPDATE Edit_Users SET AccessSuperuser= $mayS WHERE UserId = $userid";
        $dbConnection->query($q);
        echo "Updated superuser access.\n";
      }
    break;
    /* Parameters: userid */
    case 'delete':
      $userid = $dbConnection->escape_string($_POST['userid']);
      /* Checking that the user won't delete itself: */
      if($userid == session_getUid()){
        Config::error("You cannot delete yourself, sorry.");
        echo "You cannot delete yourself, sorry.";
      }else{
        /* Deleting the user: */
        $q = "DELETE FROM Edit_Users WHERE UserId = $userid";
        $dbConnection->query($q);
        echo "Deleted user: $userid";
      }
    break;
    case 'export':
      $export = array();
      $q = 'SELECT UserId, Login, Hash, AccessEdit, AccessTranslate FROM Edit_Users';
      $set = $dbConnection->query($q);
      while($row = $set->fetch_assoc())
        array_push($export, $row);
      header("Pragma: public");
      header("Expires: 0");
      header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
      header("Content-Type: application/json; charset=utf-8");
      header("Content-Disposition: attachment;filename=\"users.json\"");
      header("Content-Transfer-Encoding: binary");
      die(Config::toJSON($export));
    break;
    case 'import':
      if(count($_FILES) === 1){
        $file = file_get_contents($_FILES['import']['tmp_name']);
        $data = json_decode($file);
        foreach($data as $user){
          $UserId          = $dbConnection->escape_string($user->UserId);
          $Login           = $dbConnection->escape_string($user->Login);
          $Hash            = $dbConnection->escape_string($user->Hash);
          $AccessEdit      = $dbConnection->escape_string($user->AccessEdit);
          $AccessTranslate = $dbConnection->escape_string($user->AccessTranslate);
          $q = "INSERT INTO Edit_Users(UserId, Login, Hash, AccessEdit, AccessTranslate) "
             . "VALUES ($UserId, '$Login', '$Hash', $AccessEdit, $AccessTranslate) "
             . "ON DUPLICATE KEY UPDATE Login='$Login', Hash='$Hash'"
             . ", AccessEdit=$AccessEdit, AccessTranslate=$AccessTranslate";
          $dbConnection->query($q);
        }
        header('LOCATION: ../index.php');
      }else{
        die('Sorry, you need to supply a file.');
      }
    break;
    default: Config::error('Call to unsupported action.');
  }
