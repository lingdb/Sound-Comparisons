<?php
require_once 'DBEntry.php';
/**
  A MeaningGroup corresponds to an Entry from the MeaningGroups table.
*/
class MeaningGroup extends DBEntry{
  /**
    @return $name String
    Returns the Name of the MeaningGroup.
    The Name is translated if the ValueManager that the MeaningGroup
    was created with has a Translator that can translate it.
  */
  public function getName(){
    if($trans = $this->getValueManager()->getTranslator()->dt($this)){
      return $trans;
    }
    return $this->key;
  }
  /**
    @param [$s] Study
    @return $words Word[]
  */
  public function getWords($s = null){
    $id  = $this->id;
    $sid = $this->v->getStudy()->getId();
    if($s)
      $sid = $s->getId();
    $q = "SELECT CONCAT(IxElicitation, IxMorphologicalInstance) "
       . "FROM MeaningGroupMembers "
       . "WHERE MeaningGroupIx = $id "
       . "AND CONCAT(IxElicitation, IxMorphologicalInstance) = ANY("
         . "SELECT CONCAT(IxElicitation, IxMorphologicalInstance) FROM Words_$sid"
       . ") ORDER BY MeaningGroupMemberIx, IxElicitation ASC";
    $set = mysql_query($q, $this->dbConnection);
    $ret = array();
    while($r = mysql_fetch_row($set)){
      array_push($ret, new WordFromId($this->v, $r[0]));
    }
    return $ret;
  }
}
/** Allowes to create a MeaningGroup from it's id. */
class MeaningGroupFromId extends MeaningGroup{
  /**
    @param $v ValueManager
    @param $id String
  */
  public function __construct($v, $id){
    $this->setup($v);
    $this->id = $id;
    $q = "SELECT Name FROM MeaningGroups WHERE MeaningGroupIx = $id";
    if($r = mysql_fetch_row(mysql_query($q, $this->dbConnection))){
      $this->key = $r[0];
    }else die("No name for MeaningGroup: $id.");
  }
}
/** Allowes to create a MeaningGroup from it's key. */
class MeaningGroupFromKey extends MeaningGroup{
  /**
    @param $v ValueManager
    @param $key String
  */
  public function __construct($v, $key){
    $this->setup($v);
    $this->key = $key;
    $q = "SELECT MeaningGroupIx FROM MeaningGroups WHERE Name = '$key'";
    if($r = mysql_fetch_row(mysql_query($q, $this->dbConnection))){
      $this->id = $r[0];
    }else die("No Id for MeaningGroup with name: $key.");
  }
}
?>
