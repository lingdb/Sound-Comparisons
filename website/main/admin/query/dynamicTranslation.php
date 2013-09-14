<?php
  /**
    The following files define the fetchTranslations_$suffix
    and the savetranslation_$suffix methods.
    Both groups of functions are of the same Type.
    Functions in the form of fetchTranslations_$suffix
    return an array compatible with mkJSON defined below.
    Functions in the form of saveTranslation_$suffix
    take an entry as produced by mkJSON as a parameter
    and try to save it correctly in the database.
  */
  require_once 'dynamicTranslation/Families.php';
  require_once 'dynamicTranslation/Languages.php';
  require_once 'dynamicTranslation/LanguageStatusTypes.php';
  require_once 'dynamicTranslation/MeaningGroups.php';
  require_once 'dynamicTranslation/RegionLanguages.php';
  require_once 'dynamicTranslation/Regions.php';
  require_once 'dynamicTranslation/Studies.php';
  require_once 'dynamicTranslation/StudyTitle.php';
  require_once 'dynamicTranslation/Words.php';
  /* Setup and session verification */
  chdir('..');
  require_once 'common.php';
  session_validate($dbConnection) or die('403 Forbidden');
  session_mayTranslate($dbConnection) or die('403 Forbidden');
  /* Constants */
  define('PAGE_ITEM_LIMIT', 30);
  define('DB_CONNECTION', $dbConnection);
  /**
    Encodes the input as a json array of objects.
    See the source for a mapping of subarrays to object fields.
    @param $values String[][]
    @return String json encoded values
  */
  function mkJSON($values){
    $data = array();
    foreach($values as $v){
      $entry = array(
        'TranslationId' => $v[0] // A TranslationId from v4.Page_Translations
      , 'TableSuffix'   => $v[1] // String, a valid suffix
      , 'Study'         => $v[2] // String equal to a Name from v4.Studies
      , 'Key'           => $v[3] // An implode of key fields with delimeter ','
      , 'Source'        => $v[4] // An Obj with String fields for the original Values
      , 'Translation'   => $v[5] // An Obj with String fields for the translated Values
      , 'Description'   => $v[6] // An Obj with String fields describing the content of Source and Translation fields
      );
      array_push($data, $entry);
    }
    return json_encode($data);
  }
  /**
    Fetches the Number of entries for a given suffix in a given study
    @param  $suffix - String
    @param  $study  - String
    @return $count  - Integer
  */
  function getSuffixCount($suffix, $study){
    $q = 'SELECT 0';
    switch($suffix){
      case 'Families':
        $q = "SELECT COUNT(*) FROM Families";
      break;
      case 'Languages':
        $q = "SELECT COUNT(*) FROM Languages_$study";
      break;
      case 'LanguageStatusTypes':
        $q = "SELECT COUNT(*) FROM LanguageStatusTypes "
           . "WHERE Description != ''";
      break;
      case 'MeaningGroups':
        $q = "SELECT COUNT(*) FROM MeaningGroups";
      break;
      case 'RegionLanguages':
        $q = "SELECT COUNT(*) FROM RegionLanguages_$study "
           . "WHERE RegionGpMemberLgNameShortInThisSubFamilyWebsite != '' "
           . "AND RegionGpMemberLgNameLongInThisSubFamilyWebsite != ''";
      break;
      case 'Regions':
        $q = "SELECT COUNT(*) FROM Regions_$study";
      break;
      case 'Studies':
      case 'StudyTitle':
        $q = "SELECT COUNT(DISTINCT Name) FROM Studies";
      break;
      case 'Words':
        $q = "SELECT COUNT(*) FROM Words_$study";
      break;
    }
    $c = DB_CONNECTION->query($q)->fetch_row();
    return $c[0];
  }
  //Function to fetch $study where dummies are allowed:
  function getStudy(){
    if(!isset($_GET['Study']))
      return 'dummyStudy';
    return $dbConnection->escape_string($_GET['Study']);
  }
  //Function to fetch descriptions from Page_StaticDescription table:
  function getDescriptions($names, $dbConnection){
    $ret = array();
    foreach($names as $n){
      $q = "SELECT Description FROM Page_StaticDescription WHERE Req = '$n'";
      $r = $dbConnection->query($q)->fetch_row();
      $ret[$n] = $r[0];
    }
    return $ret;
  }
  //Actions:
  switch($_GET['action']){
    /*
      Returns a JSON array of all Names in the Studies table.
    */
    case 'fetchStudies':
      $data = array();
      $q    = "SELECT DISTINCT Name FROM Studies";
      $set  = DB_CONNECTION->query($q);
      while($r = $set->fetch_row()){
        array_push($data, $r[0]);
      }
      echo json_encode($data);
    break;
    /*
      Returns a JSON array of all the suffixes $s of Page_DynamicTranslation_$s tables.
    */
    case 'fetchSuffixes':
      echo json_encode(array(
        'Families'
      , 'Languages'
      , 'LanguageStatusTypes'
      , 'MeaningGroups'
      , 'RegionLanguages'
      , 'Regions'
      , 'Studies'
      , 'StudyTitle'
      , 'Words'));
    break;
    /*
      Returns a JSON array of OFFSET values to use with the LIMIT of PAGE_ITEM_LIMIT
      to be able to view all Entries.
      @param Study  String - Name of the Study that is targeted.
      @param Suffix String - The Table which is translated.
    */
    case 'fetchOffsets':
      $study   = getStudy();
      $suffix  = $dbConnection->escape_string($_GET['Suffix']);
      $count   = getSuffixCount($suffix, $study);
      $offsets = array();
      for($offset = 0; $offset < $count; $offset += PAGE_ITEM_LIMIT){
        array_push($offsets, $offset);
      }
      echo json_encode($offsets);
    break;
    /*
      Returns output of mkJSON.
      @param Study         String  - Name of the Study that is targeted.
      @param Suffix        String  - The Table which is translated.
      @param TranslationId Integer - Translation to use
      @param Offset        Integer - Offset to use for fetching items.
    */
    case 'fetchTranslations':
      $study   = getStudy();
      $suffix  = $dbConnection->escape_string($_GET['Suffix']);
      $tid     = $dbConnection->escape_string($_GET['TranslationId']);
      $offset  = $dbConnection->escape_string($_GET['Offset']);
      $handler = "fetchTranslations_$suffix";
      $values  = $handler($tid, $study, $offset);
      echo mkJSON($values);
    break;
    /*
      Stores the given data into the database.
      @param Translation JSON - a JSON Object like in the Array generated by mkJSON
             except that the 'Source' may be missing.
      @return Status message to be displayed to the user.
    */
    case 'storeTranslation':
      $handler     = "saveTranslation_".$_GET['TableSuffix'];
      $tid         = $dbConnection->escape_string($_GET['TranslationId']);
      $study       = $dbConnection->escape_string($_GET['Study']);
      $key         = explode(',', $_GET['Key']);
      $translation = $_GET['Translation'];
      $key         = array_map("$dbConnection->escape_string", $key);
      $translation = array_map("$dbConnection->escape_string", $translation);
      $handler($tid, $study, $key, $translation);
    break;
  }
?>
