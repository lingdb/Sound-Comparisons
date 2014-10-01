<?php
  /**
    Builds upon $SESSION
    therefore session_start() must've been called.
    This contains functions to check if a users login is valid.
  */
  /**
    @param $dbConnection mysql-resource
    @return valid Bool
  */
  function session_validate(){
    $dbConnection = Config::getConnection();
    if(!isset($_SESSION['Secret']) || !isset($_SESSION['UserId']))
      return false;
    $query = 'SELECT Login, Hash FROM Edit_Users WHERE UserId = '
      .$dbConnection->escape_string($_SESSION['UserId']);
    if($row = $dbConnection->query($query)->fetch_assoc()){
      if(password_verify($row['Login'].$row['Hash'], $_SESSION['Secret'])){
        return true;
      }
      //Fallback on md5:
      $md5 = md5($row['Login'].$row['Hash']);
      return $md5 === $_SESSION['Secret'];
    }
    return false;
  }
  /**
    Generates a session-secret against which a user can be validated.
    Notice that security is flawed anyway o.O
    @param $user String
    @param $hash String
  */
  function session_mkValid($user, $hash){
    error_log("session_mkValid($user, $hash)");
    $h = password_hash($user.$hash, PASSWORD_BCRYPT);
    if(!$h){//Fallback on md5
      $h = md5($user.$hash);
    }
    $_SESSION['Secret'] = $h;
  }
  /**
    Checks if the current login may translate languages
    @param $dbConnection mysql-resource
    @returns may Bool
  */
  function session_mayTranslate(){
    $dbConnection = Config::getConnection();
    if(!isset($_SESSION['UserId']))
      return false;
    $query = 'SELECT AccessTranslate FROM Edit_Users WHERE UserId = '
      .$dbConnection->escape_string($_SESSION['UserId']);
    if($r = $dbConnection->query($query)->fetch_assoc())
      return ($r['AccessTranslate'] == '1');
    return false;
  }
  /**
    Checks if the current login may edit the db
    @param $dbConnection mysql-resource
    @returns may Bool
  */
  function session_mayEdit(){
    $dbConnection = Config::getConnection();
    if(!isset($_SESSION['UserId']))
      return false;
    $query = 'SELECT AccessEdit FROM Edit_Users WHERE UserId = '
      .$dbConnection->escape_string($_SESSION['UserId']);
    if($r = $dbConnection->query($query)->fetch_assoc())
      return ($r['AccessEdit'] == '1');
    return false;
  }
  /**
    Returns the UserId for the current session or dies.
    It is strongly advised to check if the session is valid first.
  */
  function session_getUid(){
    if(!isset($_SESSION['UserId']))
      Config::error('UserId is not set in validate.php:session_getUid()');
    return Config::getConnection()->escape_string($_SESSION['UserId']);
  }
?>
