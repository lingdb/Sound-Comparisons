<?php
/**
  We define a function that tries to look for the current commit hash,
  and returns helpful data, if a commit hash can be found.
*/
function git_info(){
  try{
    $git = `which git`;
  }catch(Exception $e){
    $git = 'git not found';
  }
  $commit = null;
  if(preg_match('/^[^ ]+ not found$/', $git)){
    try{
      $commit = file_get_contents('../.git/ORIG_HEAD');
    }catch(Exception $e){
    }
  }else{//We got git:
    $commit = `git rev-parse HEAD`;
  }
  if($commit !== null){
    return array(
      'link' => "https://github.com/sndcomp/website/commit/$commit"
    , 'text' => 'vers.: '.substr($commit, 0, 7)
    );
  }
  return null;
}
