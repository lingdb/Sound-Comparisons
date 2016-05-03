<?php
$dir = getcwd(); chdir(__DIR__);
require_once('../config.php');
chdir($dir); unset($dir);
/**
  This is mostly a helper class for query/data.php,
  but since some of it's parts shall be used at other places also,
  the raw gathering of informations from the database is outsourced here.
  DataProvider assumes that config.php has been included before operation takes place.
*/
class DataProvider {
  /***/
  public static $soundExtensions = array('.mp3','.ogg');
  /***/
  public static $missingSounds = array();
  /**
    @param $q SQL String
    @return [[Field => Value]]
    Executes a query against the Config::getConnection.
    All rows are fetched with fetch_assoc and returned in an array in order.
  */
  public static function fetchAll($q){
    $ret = array();
    $set = Config::getConnection()->query($q);
    if($set !== false){
      while($x = $set->fetch_assoc()){
        array_push($ret, $x);
      }
    }
    return $ret;
  }
  /**
    @param $lang String Languages_$sId, FilePathPart field
    @param $word String Words_$sId, SoundFileWordIdentifierText field
    @param $pron ''||String Transcriptions_$sId, AlternativePhoneticRealisationIx field
    @param $lex  ''||String Transcriptions_$sId, AlternativeLexemIx field
    @return $paths [String] array of existing .{mp3,ogg} files with Config::$soundPath prefix
    Searches for existing SoundFiles and returns them.
  */
  public static function findSoundFiles($lang, $word, $pron = '', $lex = ''){
    if($pron !== '') $pron = '_pron'.$pron;
    if($lex  !== '') $lex  = '_lex'.$lex;
    $path = Config::$soundPath."/$lang/$lang$word$lex$pron";
    $ret  = array();
    foreach(static::$soundExtensions as $ext){
      $p = $path.$ext;
      if(file_exists($p)){
        array_push($ret, $p);
      }
    }
    if(count($ret) === 0){
      array_push(static::$missingSounds, $path.'.{mp3,ogg}');
    }
    return $ret;
  }
  /**
    @param $sId String studyId like the _$sId suffixes in the db.
    @param $t [ LanguageIx => String
              , IxElicitation => String
              , IxMorphologicalInstance => String
              , AlternativePhoneticRealisationIx => String
              , AlternativeLexemIx => String]
    @return [ lang => FilePathPart
            , word => SoundFileWordIdentifierText
            , pron => AlternativePhoneticRealisationIx || ''
            , lex  => AlternativeLexemIx || '']
    Gathers information necessary to look for a sound file.
  */
  public static function soundPathParts($sId, $t){
    $lIx = $t['LanguageIx'];
    $wId = $t['IxElicitation'].$t['IxMorphologicalInstance'];
    if(!isset($lIx) || !isset($wId))
      return array();
    $base = Config::$soundPath;
    $lq   = "SELECT FilePathPart FROM Languages_$sId WHERE LanguageIx = $lIx";
    $wq   = "SELECT SoundFileWordIdentifierText FROM Words_$sId "
          . "WHERE CONCAT(IxElicitation, IxMorphologicalInstance) = '$wId'";
    $getFirst = function($q){
      $set = DataProvider::fetchAll($q);
      if(count($set) === 0){
        Config::error("Problem with query: '$q'", true);
        return '';
      }
      return current(current($set));
    };
    return array(
      'lang' => $getFirst($lq)
    , 'word' => $getFirst($wq)
    , 'pron' => ($t['AlternativePhoneticRealisationIx'] > 1) ? $t['AlternativePhoneticRealisationIx'] : ''
    , 'lex'  => ($t['AlternativeLexemIx'] > 1) ? $t['AlternativeLexemIx'] : ''
    );
  }
  /**
    @param $sId String studyId like the _$sId suffixes in the db.
    @param $t [ LanguageIx => String
              , IxElicitation => String
              , IxMorphologicalInstance => String
              , AlternativePhoneticRealisationIx => String
              , AlternativeLexemIx => String]
    @return static::findSoundFiles(…);
    Uses static::soundPathParts to gather and sanitize data necessary for static::findSoundFiles,
    which is than executed and returned.
  */
  public static function soundPaths($sId, $t){
    $pts = static::soundPathParts($sId, $t);
    return static::findSoundFiles($pts['lang'], $pts['word'], $pts['pron'], $pts['lex']);
  }
  /**
    @return Studies [String]
    Returns an array with all studies currently known to the database in it.
  */
  public static function getStudies(){
    $studies = array();
    $set = Config::getConnection()->query('SELECT Name FROM Studies');
    while($r = $set->fetch_assoc()){
      array_push($studies, $r['Name']);
    }
    return $studies;
  }
  /**
    @return $global [complex]
    Captures a representation of all data currently in the database that is not
    dependent on a specific study or translation,
    and is not internal to the admin section.
    This representation is presented to clients at another place,
    and used troughout the session/time of knowledge by the client.
  */
  public static function getGlobal(){
    $global = array(
      'shortLinks' => array()
    , 'soundPath'  => Config::$soundPath
    );
    $queries = array(
      'contributors'                => 'SELECT * FROM Contributors'
    , 'contributorCategories'       => 'SELECT * FROM ContributorCategories'
    , 'flagTooltip'                 => "SELECT * FROM FlagTooltip WHERE FLAG != ''"
    , 'languageStatusTypes'         => 'SELECT * FROM LanguageStatusTypes'
    , 'meaningGroups'               => 'SELECT * FROM MeaningGroups'
    , 'transcrSuperscriptInfo'      => 'SELECT * FROM TranscrSuperscriptInfo'
    , 'transcrSuperscriptLenderLgs' => 'SELECT * FROM TranscrSuperscriptLenderLgs'
    , 'wikipediaLinks'              => 'SELECT * FROM WikipediaLinks'
    );
    foreach($queries as $k => $q){
      $global[$k] = static::fetchAll($q);
    }
    //Adding shortLinks:
    foreach(static::fetchAll('SELECT Name, Target FROM Page_ShortLinks') as $s){
      $global['shortLinks'][$s['Name']] = $s['Target'];
    }
    //Fixing contributor avatars:
    foreach($global['contributors'] as $k => $v){
      $prefix = 'img/contributors/';
      $inits  = $v['Initials'];
      foreach(array('.jpg','.png','.gif') as $ext){
        $file = $prefix.$inits.$ext;
        if(file_exists($file)){
          $global['contributors'][$k]['Avatar'] = $file;
          break;
        }
      }
    }
    return $global;
  }
  /**
    @param $study String Name of an entry in the Studies table
    @return $sId String
    Fetches the CONCAT(StudyIx, FamilyIx) for a given Studies Name from the db.
  */
  public static function getStudyId($study){
    $db = Config::getConnection();
    $n  = $db->escape_string($study);
    $q  = "SELECT CONCAT(StudyIx, FamilyIx) FROM Studies WHERE Name = '$n'";
    if($sId = $db->query($q)->fetch_row()){
      return current($sId);
    }
    die('Could not fetch the required study, sorry.');
  }
  /**
    @return $assoc StudyRow
    Fetches a row for a study as an assoc array from the database.
  */
  public static function getStudy($name){
    $db = Config::getConnection();
    $n  = $db->escape_string($name);
    $q  = "SELECT * FROM Studies WHERE Name = '$n'";
    return $db->query($q)->fetch_assoc();
  }
  /**
    @param $studyId StudyId as fetched with static::getStudyId()
    @return $families [{}]
    Fetches all Families that a given study belongs to,
    and returns them in order as assocs.
  */
  public static function getFamilies($studyId){
    $sId = Config::getConnection()->escape_string($studyId);
    $q = "SELECT * FROM Families "
       . "WHERE CONCAT(StudyIx, FamilyIx) "
       . "LIKE (SELECT CONCAT(REPLACE($sId, 0, ''), '%'))";
    return static::fetchAll($q);
  }
  /**
    @param $studyName String
    @return $regions [{}]
    Fetches all regions that belong to a given study.
  */
  public static function getRegions($studyName){
    $db = Config::getConnection();
    $n  = $db->escape_string($studyName);
    $q  = "SELECT * FROM Regions_$n";
    return static::fetchAll($q);
  }
  /**
    @param $studyName String
    @return $regionLanguages [{}]
    Fetches all regionLanguages that belong to a given study.
  */
  public static function getRegionLanguages($studyName){
    $db = Config::getConnection();
    $n  = $db->escape_string($studyName);
    $q  = "SELECT * FROM RegionLanguages_$n";
    return static::fetchAll($q);
  }
  /**
    @param $studyName String
    @return $languages [{}]
    Fetches all languages that belong to a given study.
  */
  public static function getLanguages($studyName){
    $db = Config::getConnection();
    $n  = $db->escape_string($studyName);
    $q  = "SELECT * FROM Languages_$n";
    $lData = static::fetchAll($q);
    return array_map('DataProvider::getLanguageContributorImages', $lData);
  }
  /**
    @param $languageData {} as fetch_assoc from Languages_* table
    @return $languageData with additional 'contributors' field
  */
  public static function getLanguageContributorImages($languageData){
    if(array_key_exists('FilePathPart', $languageData)){
      $images = array(); $i = 1; $found = true; $base = 'img/contributors/';
      while($found){
        $path = $base.$languageData['FilePathPart'].'_'.str_pad($i, 2, '0', STR_PAD_LEFT).'.jpg';
        $i++;
        $found = file_exists($path);
        if($found){
          array_push($images, $path);
        }
      }
      $languageData['ContributorImages'] = $images;
    }
    return $languageData;
  }
  /**
    @param $studyName String
    @return $words [{}]
    Fetches all words that belong to a given study.
  */
  public static function getWords($studyName){
    $db = Config::getConnection();
    $n  = $db->escape_string($studyName);
    $q  = "SELECT * FROM Words_$n";
    return static::fetchAll($q);
  }
  /**
    @param $studyId String
    @return MeaningGroupMembers [[MeaningGroupIx, MeaningGroupMemberIx, IxElicitation, IxMorphologicalInstance]]
    Fetches all MeaningGroupMembers that belong to a given studyId.
  */
  public static function getMeaningGroupMembers($studyId){
    $sId = Config::getConnection()->escape_string($studyId);
    $q   = "SELECT MeaningGroupIx, MeaningGroupMemberIx, IxElicitation, IxMorphologicalInstance FROM MeaningGroupMembers "
         . "WHERE CONCAT(StudyIx, FamilyIx) = $sId";
    return static::fetchAll($q);
  }
  /**
    @param $studyName String
    @return transcriptions [CONCAT(LanguageIx,IxElicitation,IxMorphologicalInstance) => {}]
    Fetches all transcriptions that belong to a given study.
    Since transcriptions may occupy more than just one row in the database,
    their contents are merged together, and they are not kept in order,
    but are given keys by fields that identify them precisely.
    Since there are cases where we expect transcriptions to exist,
    but they're not given by the database, this method makes use of
    static::getDummyTranscriptions to generate DummyTranscriptions,
    and fill them in where the expected key doesn't exist.
  */
  public static function getTranscriptions($studyName){
    $db  = Config::getConnection();
    $n   = $db->escape_string($studyName);
    $q   = "SELECT * FROM Transcriptions_$n";
    $ret = array();
    $set = static::fetchAll($q);
    if(count($set) > 0){
      foreach($set as $t){
        $tKey = $t['LanguageIx'].$t['IxElicitation'].$t['IxMorphologicalInstance'];
        $t['soundPaths'] = static::soundPaths($n, $t);
        //Updating RecordingMissing, iff necessary:
        if(($t['RecordingMissing'] === 0 && count($t['soundPaths']) > 0)
          || ($t['RecordingMissing'] === 1 && count($t['soundPaths']) === 0)){
          //Flip RecordingMissing:
          $flip = ($t['RecordingMissing'] === 0) ? 1 : 0;
          $q = "UPDATE Transcriptions_$n "
             . "SET RecordingMissing = $flip "
             . "WHERE StudyIx = ".$t['StudyIx']
             . " AND FamilyIx = ".$t['FamilyIx']
             . " AND IxElicitation = ".$t['IxElicitation']
             . " AND IxMorphologicalInstance = ".$t['IxMorphologicalInstance']
             . " AND LanguageIx = ".$t['LanguageIx'];
          $db->query($q);
        }
        //Merging transcriptions:
        if(array_key_exists($tKey, $ret)){
          $old = $ret[$tKey];
          foreach($t as $k => $v){
            if(array_key_exists($k, $old)){
              $o = $old[$k];
              if(isset($o) && $o !== '' && $o !== $v){
                $t[$k] = array($o, $v);
              }else if(is_array($v) && is_array($o)){
                $t[$k] = array_merge($o, $v);
              }
            }
          }
        }
        $ret[$tKey] = $t;
      }
      //Handling dummy transcriptions:
      foreach(static::getDummyTranscriptions($studyName) as $t){
        $tKey = $t['LanguageIx'].$t['IxElicitation'].$t['IxMorphologicalInstance'];
        if(!array_key_exists($tKey, $ret)){
          $ret[$tKey] = $t;
        }
      }
    }
    return $ret;
  }
  /**
    @param $studyName String
    @return dummyTranscriptions [{}]
    Output has the same form as that of static::getTranscriptions,
    but is for transcription entries that may not exist.
    static::getTranscriptions merges the outputs of this method
    into its return.
  */
  public static function getDummyTranscriptions($studyName){
    $db = Config::getConnection();
    //Add dummy transcriptions:
    $dummies = array();
    //Fetching study related data:
    $q = "SELECT StudyIx, FamilyIx FROM Studies WHERE Name='$studyName' LIMIT 1";
    $study = current(static::fetchAll($q));
    if($study === false) return $dummies;
    //Handling languages without transcriptions:
    $q = "SELECT L.LanguageIx, W.IxElicitation, W.IxMorphologicalInstance, L.FilePathPart, W.SoundFileWordIdentifierText "
       . "FROM Languages_$studyName AS L CROSS JOIN Words_$studyName AS W "
       . "WHERE CONCAT(L.LanguageIx, W.IxElicitation, W.IxMorphologicalInstance) "
       . "NOT IN (SELECT CONCAT(LanguageIx, IxElicitation, IxMorphologicalInstance) FROM Transcriptions_$studyName)";
    $qs = static::fetchAll($q);
    //Handling resulting pairs:
    foreach($qs as $entry){
      $files = static::findSoundFiles($entry['FilePathPart'], $entry['SoundFileWordIdentifierText']);
      $missing = (count($files) === 0) ? 1 : 0;
      //Returning saving found dummies:
      $q = "INSERT INTO Transcriptions_$studyName "
         . "(StudyIx, FamilyIx, IxElicitation, IxMorphologicalInstance, LanguageIx, RecordingMissing) "
         . "VALUES ({$study['StudyIx']},{$study['FamilyIx']},{$study['IxElicitation']},{$study['IxMorphologicalInstance']},{$study['LanguageIx']},$missing)";
      $db->query($q);
      //Filtering
      if($missing === 1) continue;
      //Adding to dummy list:
      array_push($dummies, array(
        'isDummy' => true
      , 'LanguageIx' => $entry['LanguageIx']
      , 'IxElicitation' => $entry['IxElicitation']
      , 'IxMorphologicalInstance' => $entry['IxMorphologicalInstance']
      , 'soundPaths' => $files
      ));
    }
    return $dummies;
  }
  /**
    @param $studyId String CONCAT(StudyIx, FamilyIx)
    @return $defaults [ language => LanguageIx
                      , word => CONCAT(IxElicitation,IxMorpholigcalInstance)
                      , languages => [LanguageIx]
                      , words => [CONCAT(IxElicitation,IxMorpholigcalInstance)]
                      , excludeMap => [LanguageIx]]
    Given a studyId, this method fetches the default words and languages.
  */
  public static function getDefaults($studyId){
    $db  = Config::getConnection();
    $sId = $db->escape_string($studyId);
    $ret = array();
    //Single queries:
    foreach(array(
      //Default_Languages
      'language' => "SELECT LanguageIx FROM Default_Languages "
                  . "WHERE CONCAT(StudyIx, FamilyIx) "
                  . "LIKE (SELECT CONCAT(REPLACE($sId, 0, ''), '%')) "
                  . "LIMIT 1"
      //Default_Words
    , 'word'     => "SELECT IxElicitation, IxMorphologicalInstance FROM Default_Words "
                  . "WHERE CONCAT(StudyIx, FamilyIx) "
                  . "LIKE (SELECT CONCAT(REPLACE($sId, 0, ''), '%')) "
                  . "LIMIT 1"
    ) as $k => $q){
      $ret[$k] = $db->query($q)->fetch_assoc();
    }
    //Multiple queries:
    foreach(array(
      //Default_Multiple_Languages
      'languages'  => "SELECT LanguageIx FROM Default_Multiple_Languages "
                    . "WHERE CONCAT(StudyIx, FamilyIx) "
                    . "LIKE (SELECT CONCAT(REPLACE($sId, 0, ''), '%'))"
      //Default_Multiple_Words
    , 'words'      => "SELECT IxElicitation, IxMorphologicalInstance FROM Default_Multiple_Words "
                    . "WHERE CONCAT(StudyIx, FamilyIx) "
                    . "LIKE (SELECT CONCAT(REPLACE($sId, 0, ''), '%'))"
      //Default_Languages_Exclude_Map
    , 'excludeMap' => "SELECT LanguageIx FROM Default_Languages_Exclude_Map "
                    . "WHERE CONCAT(StudyIx, FamilyIx) "
                    . "LIKE (SELECT CONCAT(REPLACE($sId, 0, ''), '%'))"
    ) as $k => $q){
      $ret[$k] = static::fetchAll($q);
    }
    return $ret;
  }
  /***/
  public static function getLastImport(){
    $q = 'SELECT UNIX_TIMESTAMP(Time) FROM Edit_Imports ORDER BY TIME DESC LIMIT 1';
    $t = static::fetchAll($q);
    if(count($t) > 0){
      return current(current($t));
    }
    Config::error('Query failed in DataProvider::getLastImport()');
    return 0;
  }
  /***/
  public static function getStudyChunk($studyName){
    //Provide complete data for a single study:
    $sId = DataProvider::getStudyId($studyName);
    //The representation that will be returned:
    return array(
      'study'               => DataProvider::getStudy($studyName)
    , 'families'            => DataProvider::getFamilies($sId)
    , 'regions'             => DataProvider::getRegions($studyName)
    , 'regionLanguages'     => DataProvider::getRegionLanguages($studyName)
    , 'languages'           => DataProvider::getLanguages($studyName)
    , 'words'               => DataProvider::getWords($studyName)
    , 'meaningGroupMembers' => DataProvider::getMeaningGroupMembers($sId)
    , 'transcriptions'      => DataProvider::getTranscriptions($studyName)
    , 'defaults'            => DataProvider::getDefaults($sId)
    );
  }
}
