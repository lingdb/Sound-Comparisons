<?php
//Setup:
chdir('..');
require_once 'config.php';
require_once 'stopwatch.php';
require_once 'valueManager/RedirectingValueManager.php';
$dbConnection = Config::getConnection();
$valueManager = RedirectingValuemanager::getInstance();
/*
  For the site to do as much as possible in the browser, it's crucial to have a data representation in JSON,
  so that we can use and manipulate stuff in JavaScript with ease.
  After reading http://alistapart.com/article/application-cache-is-a-douchebag I came to the conclusion,
  that ApplicationCache is not what we want for our dynamic content,
  but we'll stick with our current practise of storing stuff in localStorage.
  However, since the main data that shall be provided by this file
  may be bigger than fits localStorage, we choose a different route:
  1.: We offer a list of studies.
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
*/
if(!array_key_exists('study',$_GET)){
  /*
    Provide a list of all studies,
    but normally it'll probably be simpler to parse the studies from the page initially.
  */
  $studies = array();
  $set = $dbConnection->query('SELECT Name FROM Studies');
  while($r = $set->fetch_assoc()){
    array_push($studies, $r['Name']);
  }
  echo json_encode(array(
    'studies' => $studies
  , 'description' => 'Give one of the studies as get parameter study.'
  ));
}else{
  //Provide complete data for a single study:
  $n = $dbConnection->escape_string($_GET['study']);
  $q = "SELECT StudyIx, FamilyIx, SubFamilyIx, Name, "
     . "DefaultTopLeftLat, DefaultTopLeftLon, DefaultBottomRightLat, DefaultBottomRightLon, "
     . "ColorByFamily, SecondRfcLg FROM Studies WHERE Name = '$n'";
  $study = $dbConnection->query($q)->fetch_assoc();
  if(!$study){
    die('Could not fetch the required study, sorry.');
  }
  echo json_encode($study);
}
?>
