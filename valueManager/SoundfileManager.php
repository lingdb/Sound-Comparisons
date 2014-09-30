<?php
require_once 'SubManager.php';

abstract class SoundfileManager extends SubManager{
  private $soundfiles = null;
  /**
    Returns the file extensions for soundfiles in transcriptions.
    These might be '.mp3' or '.ogg' for example,
    and are choosen depending on the Useragent.
    @return $soundFiles String[]
  */
  public function getSoundFiles(){
    return $this->soundFiles;
  }
}

class InitSoundfileManager extends SoundfileManager{
  /**
    There are soundfiles available as .mp3 and .ogg.
    The SoundFileManager detects the browser to decide which one to use.
    If it can't decide, both will be put in $this->soundFiles.
    @param $v ValueManager
  */
  public function __construct($v){
    $this->setValueManager($v);
    $soundFiles = null;
    if(isset($_SERVER['HTTP_USER_AGENT'])){
      $uAgent = $_SERVER['HTTP_USER_AGENT'];
      $regs = array( // Src: http://www.w3schools.com/html5/html5_audio.asp
          '/MSIE/i'    => array('.mp3')  // Internet Explorer
        , '/firefox/i' => array('.ogg')  // Firefox
        , '/chrome/i'  => array('.ogg')  // Chrome
        , '/safari/i'  => array('.mp3')  // Safari
        , '/opera/i'   => array('.ogg')  // Opera
      );
      //Detecting for obvious matches:
      foreach($regs as $r => $sf){
        if(preg_match($r, $uAgent)){
          $soundFiles = $sf;
          break;
        }
      }
    }
    //Defaulting to both.
    if(!$soundFiles)
      $soundFiles = array('.ogg','.mp3');
    $this->soundFiles = $soundFiles;
  }
  /***/
  public function getName(){return "SoundfileManager";}
}

?>
