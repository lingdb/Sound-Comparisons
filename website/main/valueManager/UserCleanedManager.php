<?php
require_once 'SubManager.php';

abstract class UserCleanedManager extends SubManager{
  protected $userCleaned = false;
  /**
    Does not keep referential transparency.
    @return $v ValueManager
  */
  public function setUserCleaned(){
    $this->userCleaned = true;
    return $this->gvm();
  }
  /***/
  public function unsetUserCleaned(){
    $this->userCleaned = false;
    return $this->gvm();
  }
  /** @return $userCleaned Bool */
  public function isUserCleaned(){
    return $this->userCleaned;
  }
  /***/
  public function pack(){
    if($this->isUserCleaned())
      return array('userCleaned' => 1);
    return array();
  }
}

class InitUserCleanedManager extends UserCleanedManager{
  /** @param $v ValueManager */
  public function __construct($v){
    $this->setValueManager($v);
    if(isset($_GET['userCleaned'])){
      $uc = $this->getConnection()->escape_string($_GET['userCleaned']);
      $this->userCleaned = ($uc == 1);
    }
  }
  /** Implements SubManager:getName() */
  public function getName(){return 'UserCleanedManager';}
}

?>
