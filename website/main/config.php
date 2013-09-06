<?php
require_once 'extern/underscore.php';
require_once 'database/Database.php';
/* A class to bundle all configuration issues for the website. */
class Config {
  /* Configuration: */
  private $server = "localhost";
  private $user   = "root";
  private $passwd = "1234";
  private $db     = "v4";
  /* Accessable values: */
  private $dbConnection  = null;
  private $dbiConnection = null;
  private $flags_enabled = false;
  private $soundPath     = "../sound";
  private $downloadPath  = "export/download";
  /* Functions to connect to the db: */
  private function connectMysql(){
    $this->dbConnection = mysql_connect($this->server, $this->user, $this->passwd) or die(mysql_error());
    if(!mysql_select_db($this->db, $this->dbConnection)){
      die("Could not select db '$this->db'!" . mysql_error());
    }
    $q = mysql_query('SET names \'utf8\'', $this->dbConnection);
  }
  private function connectMysqlI(){
    $this->dbiConnection = new mysqli($this->server, $this->user, $this->passwd, $this->db); // Used in files.php
    mysqli_set_charset($this->dbiConnection, 'utf8');
  }
  /* Getter functions to fetch config values: */
  public function getConnection(){
    if(is_null($this->dbConnection))
      $this->connectMysql();
    return $this->dbConnection;
  }
  public function getIConnection(){
    if(is_null($this->dbiConnection))
      $this->connectMysqlI();
    return $this->dbiConnection;
  }
  public function getFlags(){
    return $this->flags_enabled;
  }
  public function getSoundPath(){
    return $this->soundPath;
  }
  public function getDownloadPath(){
    return $this->downloadPath;
  }
  /* Necessary for Admin features: */
  public function overwriteLogin($login){
    if(array_key_exists('server',$login) && is_string($login['server']))
      $this->server = $login['server'];
    if(array_key_exists('user',$login) && is_string($login['user']))
      $this->user = $login['user'];
    if(array_key_exists('passwd',$login) && is_string($login['passwd']))
      $this->passwd = $login['passwd'];
    if(array_key_exists('db',$login) && is_string($login['db']))
      $this->db = $login['db'];
  }
}
/* The config instance: */
$config = new Config();
?>
