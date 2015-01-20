<?php
  /**
    This script tries to find invalid sound files and to produce commands to rename them to their expected names.
    First argument needs to be the studyname.
  */
  require_once '../config.php';
  $dbConnection = Config::getConnection();
  /* Check for arguments: */
  if(count($argv) <= 3){
    die("Usage: correctSoundfiles.php <studyname> [soundFiles]");
  }
  /* Mapping arguments: */
  $study = $argv[1];
  $files = array();
  for($i = 2; $i < count($argv); $i++){
    array_push($files, $argv[$i]);
  }
  /* Mapping suffixes to other suffixes: */
  $suffixMap = array();
  $q = "SELECT DISTINCT SoundFileWordIdentifierText FROM Words_$study";
  $set = $dbConnection->query($q);
  while($r = $set->fetch_row()){
    $suffix = $r[0];
    $s = '';
    foreach(explode('_', $suffix) as $part){
      if($part === '') continue;
      $s .= '_'.$part;
      if($s !== $suffix){
        $suffixMap[$s] = $suffix;
      }
    }
  }
  /* Processing files: */
  //https://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
  function endsWith($haystack, $needle){
    $len = strlen($needle);
    if($len === 0) return true;
    return (substr($haystack, -$len) === $needle);
  }
  foreach($files as $file){
    $name = substr($file, 0, -4);//Dropping .{wav,mp3,ogg}
    foreach($suffixMap as $current => $should){
      if(endsWith($name, $current)){
        $newname = substr($name, 0, -strlen($current)).$should;
        $ext = substr($file, -4);
        echo "git mv $file $newname$ext\n";
        break;
      }
    }
  }
?>
