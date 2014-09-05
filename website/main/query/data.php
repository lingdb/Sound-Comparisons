<?php
//Setup:
chdir('..');
require_once 'config.php';
require_once 'stopwatch.php';
require_once 'valueManager/RedirectingValueManager.php';
$dbConnection = Config::getConnection();
$valueManager = RedirectingValuemanager::getInstance();
//Helper functions:
function fetchAll($q){
  $ret = array();
  $set = Config::getConnection()->query($q);
  while($x = $set->fetch_assoc()){
    array_push($ret, $x);
  }
  return $ret;
}
function soundPaths($sId, $t){
  $lIx  = $t['LanguageIx'];
  $wId  = $t['IxElicitation'].$t['IxMorphologicalInstance'];
  if(!isset($lIx) || !isset($wId))
    return array();
  $base = Config::$soundPath;
  $lq   = "SELECT FilePathPart FROM Languages_$sId WHERE LanguageIx != $lIx";
  $wq   = "SELECT SoundFileWordIdentifierText FROM Words_$sId "
        . "WHERE CONCAT(IxElicitation, IxMorphologicalInstance) = '$wId'";
  //error_log($lq);
  $lang = current(current(fetchAll($lq)));
  //error_log($wq);
  $word = current(current(fetchAll($wq)));
  $path = "$base/$lang/$lang$word";
  $ret  = array();
  foreach(array('.mp3', '.ogg') as $ext){
    $p = $path.$ext;
    if(file_exists($p)){
      array_push($ret, $p);
    }
  }
  return $ret;
}
/*
  For the site to do as much as possible in the browser, it's crucial to have a data representation in JSON,
  so that we can use and manipulate stuff in JavaScript with ease.
  After reading http://alistapart.com/article/application-cache-is-a-douchebag I came to the conclusion,
  that ApplicationCache is not what we want for our dynamic content,
  but we'll stick with our current practise of storing stuff in localStorage.
  However, since the main data that shall be provided by this file
  may be bigger than fits localStorage, we choose a different route:
  1.: We offer a list of studies, and also global data applying to each study.
  2.: Each study can be fetched separately.
  3.: JavaScript will tack a timestamp on each study,
      so that we can drop older studies from localStorage,
      in case that we're running out of space.
  4.: The data for each study thus consists of the following things:
      - Name and basic data for the study itself
      - A list of Families in the Study
      - A list of Regions per Family
      - A list of Languages per Region
      - A list of Words per Study
      - A list of Transcriptions per pair of Word and Language
      - Defaults for the Study
*/
if(array_key_exists('global',$_GET)){
  /*
    Provide a list of all studies,
    but normally it'll probably be simpler to parse the studies from the page initially.
  */
  $studies = array();
  $set = $dbConnection->query('SELECT Name FROM Studies');
  while($r = $set->fetch_assoc()){
    array_push($studies, $r['Name']);
  }
  //Fetching study independant data:
  $global = array(
    'contributors'                => fetchAll('SELECT * FROM Contributors')
  , 'flagTooltip'                 => fetchAll("SELECT * FROM FlagTooltip WHERE FLAG != ''")
  , 'languageStatusTypes'         => fetchAll('SELECT * FROM LanguageStatusTypes')
  , 'meaningGroups'               => fetchAll('SELECT * FROM MeaningGroups')
  , 'transcrSuperscriptInfo'      => fetchAll('SELECT * FROM TranscrSuperscriptInfo')
  , 'transcrSuperscriptLenderLgs' => fetchAll('SELECT * FROM TranscrSuperscriptLenderLgs')
  , 'wikipediaLinks'              => fetchAll('SELECT * FROM WikipediaLinks')
  , 'soundPath'                   => Config::$soundPath
  );
  //Done:
  echo json_encode(array(
    'studies' => $studies
  , 'global'  => $global
  ));
}else if(array_key_exists('study',$_GET)){
  $ret = array(
    'study'               => null
  , 'families'            => array()
  , 'regions'             => array()
  , 'regionLanguages'     => array()
  , 'languages'           => array()
  , 'words'               => array()
  , 'meaningGroupMembers' => array()
  , 'transcriptions'      => array()
  , 'defaults'            => array(
      'language'   => null
    , 'word'       => null
    , 'languages'  => array()
    , 'words'      => array()
    , 'excludeMap' => array()
    )
  );
  //Provide complete data for a single study:
  $n = $dbConnection->escape_string($_GET['study']);
  $q = "SELECT CONCAT(StudyIx, FamilyIx) FROM Studies WHERE Name = '$n'";
  $sId = $dbConnection->query($q)->fetch_row();
  if(!$sId){
    die('Could not fetch the required study, sorry.');
  }else{
    $sId = current($sId);
  }
  //Fetching the study:
  $q = "SELECT * FROM Studies WHERE Name = '$n'";
  $ret['study'] = $dbConnection->query($q)->fetch_assoc();
  //Fetching the families:
  $q = "SELECT * FROM Families "
     . "WHERE CONCAT(StudyIx, FamilyIx) "
     . "LIKE (SELECT CONCAT(REPLACE($sId, 0, ''), '%'))";
  $ret['families'] = fetchAll($q);
  //Fetching Regions:
  $q = "SELECT * FROM Regions_$n";
  $ret['regions'] = fetchAll($q);
  //Fetching RegionLanguages:
  $q = "SELECT * FROM RegionLanguages_$n";
  $ret['regionLanguages'] = fetchAll($q);
  //Fetching Languages:
  $q = "SELECT * FROM Languages_$n";
  $ret['languages'] = fetchAll($q);
  //Fetching Words:
  $q = "SELECT * FROM Words_$n";
  $ret['words'] = fetchAll($q);
  //Fetching MeaningGroupMembers:
  $q = "SELECT MeaningGroupIx, MeaningGroupMemberIx, IxElicitation, IxMorphologicalInstance  FROM MeaningGroupMembers "
     . "WHERE CONCAT(StudyIx, FamilyIx) = $sId";
  $ret['meaningGroupMembers'] = fetchAll($q);
  //Fetching Transcriptions:
  $q = "SELECT * FROM Transcriptions_$n";
  $set = $dbConnection->query($q);
  while($t = $set->fetch_assoc()){
    $tKey = $t['LanguageIx'].$t['IxElicitation'].$t['IxMorphologicalInstance'];
    if(array_key_exists($tKey, $ret['transcriptions'])){
      //Merging transcriptions:
      $old = $ret['transcriptions'][$tKey];
      foreach($t as $k => $v){
        if(array_key_exists($k, $old)){
          $o = $old[$k];
          if(isset($o) && $o !== '' && $o !== $v){
            $t[$k] = array($o, $v);
          }
        }
      }
    }else{
      $t['soundPaths'] = soundPaths($n, $t);
    }
    $ret['transcriptions'][$tKey] = $t;
  }
  //Fetching Defaults:
  //Default_Languages
  $q = "SELECT LanguageIx FROM Default_Languages "
     . "WHERE CONCAT(StudyIx, FamilyIx) = $sId";
  $ret['defaults']['language'] = $dbConnection->query($q)->fetch_assoc();
  //Default_Words
  $q = "SELECT IxElicitation, IxMorphologicalInstance FROM Default_Words "
     . "WHERE CONCAT(StudyIx, FamilyIx) = $sId";
  $ret['defaults']['word'] = $dbConnection->query($q)->fetch_assoc();
  //Default_Multiple_Languages
  $q = "SELECT LanguageIx FROM Default_Multiple_Languages "
     . "WHERE CONCAT(StudyIx, FamilyIx) = $sId";
  $ret['defaults']['languages'] = fetchAll($q);
  //Default_Multiple_Words
  $q = "SELECT IxElicitation, IxMorphologicalInstance FROM Default_Multiple_Words "
     . "WHERE CONCAT(StudyIx, FamilyIx) = $sId";
  $ret['defaults']['words'] = fetchAll($q);
  //Default_Languages_Exclude_Map
  $q = "SELECT LanguageIx FROM Default_Languages_Exclude_Map "
     . "WHERE CONCAT(StudyIx, FamilyIx) = $sId";
  $ret['defaults']['excludeMap'] = fetchAll($q);
  //Done:
  echo json_encode($ret);
}else{
  $q = 'SELECT UNIX_TIMESTAMP(Time) FROM Edit_Imports ORDER BY TIME DESC LIMIT 1';
  $time = current(current(fetchAll($q)));
  echo json_encode(array(
    'lastUpdate'  => $time
  , 'Description' => 'Add a global parameter to fetch global data, '
                   . 'and add a study parameter to fetch a study.'
  ));
}
?>
