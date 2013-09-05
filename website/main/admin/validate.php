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
  function session_validate($dbConnection){
    if(!isset($_SESSION['Secret']) || !isset($_SESSION['UserId']))
      return false;
    $query = 'SELECT Login, Hash FROM Edit_Users WHERE UserId = '
      .mysql_real_escape_string($_SESSION['UserId']);
    if($row = mysql_fetch_assoc(mysql_query($query, $dbConnection)))
      return ($_SESSION['Secret'] == md5(''.$row['Login'].$row['Hash']));
    return false;
  }
  /**
    Generates a session-secret against which a user can be validated.
    Notice that security is flawed anyway o.O
    @param $user String
    @param $hash String
  */
  function session_mkValid($user, $hash){
    $_SESSION['Secret'] = md5($user.$hash);
  }
  /**
    Checks if the current login may translate languages
    @param $dbConnection mysql-resource
    @returns may Bool
  */
  function session_mayTranslate($dbConnection){
    if(!isset($_SESSION['UserId']))
      return false;
    $query = 'SELECT AccessTranslate FROM Edit_Users WHERE UserId = '
      .mysql_real_escape_string($_SESSION['UserId']);
    if($r = mysql_fetch_assoc(mysql_query($query, $dbConnection)))
      return ($r['AccessTranslate'] == '1');
    return false;
  }
  /**
    Checks if the current login may edit the db
    @param $dbConnection mysql-resource
    @returns may Bool
  */
  function session_mayEdit($dbConnection){
    if(!isset($_SESSION['UserId']))
      return false;
    $query = 'SELECT AccessEdit FROM Edit_Users WHERE UserId = '
      .mysql_real_escape_string($_SESSION['UserId']);
    if($r = mysql_fetch_assoc(mysql_query($query, $dbConnection)))
      return ($r['AccessEdit'] == '1');
    return false;
  }
  /**
    Returns the UserId for the current session or dies.
    It is strongly advised to check if the session is valid first.
  */
  function session_getUid(){
    if(!isset($_SESSION['UserId']))
      die('UserId is not set in validate.php:session_getUid()');
    return mysql_real_escape_string($_SESSION['UserId']);
  }
?>
