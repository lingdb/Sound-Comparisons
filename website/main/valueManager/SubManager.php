<?php

abstract class SubManager{
  protected $v;
  /**
    @return $v ValueManager
  */
  protected function getValueManager(){
    return $this->v;
  }
  /**
    @param $v ValueManager
  */
  public function setValueManager($v){
    $this->v = $v;
  }
  /**
    @param $v ValueManager
    @return $c SubManager
  */
  public function updateValueManager($v){
    $c = clone $this;
    $c->v = $v;
    return $c;
  }
  /**
    @return $dbConnection
  */
  protected function getConnection(){
    return $this->v->getConnection();
  }
  /**
    Helper function for toArray.
    A distinct problem is that these Strings can also contain ',' which causes problems when using implode.
    Therefore I use '#44' to replace ','.
    @param $x String input
    @return String encoded
  */
  protected function encodeUrl($x){
    return rawurlencode(preg_replace('/,/','#44',$x));
  }
  /**
    Helper function which performs the inverse of encodeUrl
    @param $x String input
    @return String decoded
  */
  protected function decodeUrl($x){
    return preg_replace('/#44/', ',', mysql_real_escape_string(rawurldecode($x)));
  }
  /**
    The name used to identify the instance in the ValueManager
    @return $name String
  */
  abstract public function getName();
  /**
    Only works if it's overwritten by it's children.
    Returns something, typically an Array or a String
    that is usefull for ValueManager:toArray().
    @return $foo MISC
  */
  public function pack(){
    die('Call to SubManager:pack() which has to be overwritten but is not.');
  }
  /*-- Shortcut functions below: --*/
  protected function gvm(){
    return $this->getValueManager();
  }
}

?>
