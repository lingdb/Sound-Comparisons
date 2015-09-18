<?php
/**
  A class with static functions that allow us
  to gather some information regarding the local git repo.
*/
class Git {
  /**
    Provides memoization for getGit.
  */
  private static $hasGit = null;
  /**
    @return git String || null
    returns the path to the local git installation, if possible.
  */
  public static function hasGit(){
    $git = 'git not found';
    if(self::$hasGit === null){
      try{
        $git = `which git`;
      }catch(Exception $e){
        $git = 'git not found';
      }
      if(preg_match('/^[^ ]+ not found$/', $git)){
        self::$hasGit = false;
      }else{
        self::$hasGit = true;
      }
    }
    return self::$hasGit;
  }
  /**
    @param $gitDir String path to .git directory
    @return $commit {link :: String, text :: String} || null
    If self::hasGit() is true, $gitDir will be ignored.
  */
  public static function getCommit($gitDir = '.git'){
    $commit = null;
    if(self::hasGit()){
      $commit = `git rev-parse HEAD`;
    }else{//No git, work around:
      try{
        $commit = file_get_contents($gitDir.'/ORIG_HEAD');
      }catch(Exception $e){}
    }
    if($commit !== null){
      return array(
        'link' => "https://github.com/sndcomp/website/commit/$commit"
      , 'text' => 'vers.: '.substr($commit, 0, 7)
      );
    }
    return null;
  }
  /**
    @param $gitDir String path to .git directory
    @return $branch String || null
    If self::hasGit() is true, $gitDir will be ignored.
  */
  public static function getBranch($gitDir = '.git'){
    $branch = null;
    if(self::hasGit()){//We've got us some git:
      $branch = `git symbolic-ref --short HEAD`;
    }else{
      $branch = file_get_contents($gitDir.'/HEAD');
    }
    //$branch may be like 'refs/heads/master\n' instead of 'master\n':
    $branch = array_pop(explode('/', $branch));
    //Removing trailing newline:
    return rtrim($branch, "\n");
  }
}
