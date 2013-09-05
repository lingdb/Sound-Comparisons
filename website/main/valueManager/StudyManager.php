<?php
require_once 'SubManager.php';

abstract class StudyManager extends SubManager{
  protected $study = null;
  /** @return Study */
  public function getStudy(){
    return $this->study;
  }
  /**
    @return Study[]
  */
  public function getStudies(){
    $studies = array();
    $set = mysql_query('SELECT DISTINCT Name FROM Studies' ,$this->gvm()->getConnection());
    while($r = mysql_fetch_row($set)){
      array_push($studies, new StudyFromKey($this->gvm(), $r[0]));
    }
    return $studies;
  }
  /**
    @param study Study
    @return $v ValueManager
  */
  public function setStudy($study){
    $s = clone $this;
    $s->study = $study;
    return $this->gvm()->setM($s);
  }
  /**
   * @param study Study
   * @return has Bool
   * */
  public function hasStudy($study){
    if(!isset($this->study) || $study == null)
      return false;
    return ($study->getId() == $this->study->getId());
  }
  /** Overwrites SubManager:pack() */
  public function pack(){
    return array('study' => $this->study->getKey());
  }
}

class InitStudyManager extends StudyManager{
  /**
    @param $v ValueManager
  */
  public function __construct($v){
    $this->setValueManager($v);
    if(!isset($_GET['study'])){
      $this->study = new StudyFromKey($this->gvm(), "Germanic");
    }else{
      $s = mysql_real_escape_string($_GET['study']);
      $this->study = new StudyFromKey($this->gvm(), $s);
    }
  }
  /***/
  public function getName(){return "StudyManager";}
}

?>
