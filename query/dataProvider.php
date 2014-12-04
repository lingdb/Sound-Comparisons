<?php
/**
  This is mostly a helper class for query/data.php,
  but since some of it's parts shall be used at other places also,
  the raw gathering of informations from the database is outsourced here.
  DataProvider assumes that config.php has been included before operation takes place.
*/
class DataProvider {
  /***/
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
  /***/
  public static function findSoundFiles($sId, $lang, $word, $pron = '', $lex = ''){
    if($pron !== '') $pron = '_pron'.$pron;
    if($lex  !== '') $lex  = '_lex'.$lex;
    $path = Config::$soundPath."/$lang/$lang$word$lex$pron";
    $ret  = array();
    foreach(array('.mp3', '.ogg') as $ext){
      $p = $path.$ext;
      if(file_exists($p)){
        array_push($ret, $p);
      }
    }
    return $ret;
  }
  /***/
  public static function soundPaths($sId, $t){
    $lIx  = $t['LanguageIx'];
    $wId  = $t['IxElicitation'].$t['IxMorphologicalInstance'];
    if(!isset($lIx) || !isset($wId))
      return array();
    $base = Config::$soundPath;
    $lq   = "SELECT FilePathPart FROM Languages_$sId WHERE LanguageIx = $lIx";
    $wq   = "SELECT SoundFileWordIdentifierText FROM Words_$sId "
          . "WHERE CONCAT(IxElicitation, IxMorphologicalInstance) = '$wId'";
    $getFirst = function($q){
      $set = DataProvider::fetchAll($q);
      if(count($set) === 0){
        error_log("Problem with query: '$q'");
        return '';
      }
      return current(current($set));
    };
    $lang = $getFirst($lq);
    $word = $getFirst($wq);
    $pron = ($t['AlternativePhoneticRealisationIx'] > 1) ? $t['AlternativePhoneticRealisationIx'] : '';
    $lex  = ($t['AlternativeLexemIx'] > 1) ? $t['AlternativeLexemIx'] : '';
    return DataProvider::findSoundFiles($sId, $lang, $word, $pron, $lex);
  }
  /***/
  public static function getStudies(){
    $studies = array();
    $set = Config::getConnection()->query('SELECT Name FROM Studies');
    while($r = $set->fetch_assoc()){
      array_push($studies, $r['Name']);
    }
    return $studies;
  }
  /***/
  public static function getGlobal(){
    $global = array(
      'shortLinks' => array()
    , 'soundPath'  => Config::$soundPath
    );
    $queries = array(
      'contributors'                => 'SELECT * FROM Contributors'
    , 'flagTooltip'                 => "SELECT * FROM FlagTooltip WHERE FLAG != ''"
    , 'languageStatusTypes'         => 'SELECT * FROM LanguageStatusTypes'
    , 'meaningGroups'               => 'SELECT * FROM MeaningGroups'
    , 'transcrSuperscriptInfo'      => 'SELECT * FROM TranscrSuperscriptInfo'
    , 'transcrSuperscriptLenderLgs' => 'SELECT * FROM TranscrSuperscriptLenderLgs'
    , 'wikipediaLinks'              => 'SELECT * FROM WikipediaLinks'
    );
    foreach($queries as $k => $q){
      $global[$k] = DataProvider::fetchAll($q);
    }
    //Adding shortLinks:
    foreach(DataProvider::fetchAll('SELECT Name, Target FROM Page_ShortLinks') as $s){
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
  /***/
  public static function getStudyId($study){
    $db  = Config::getConnection();
    $n   = $db->escape_string($study);
    $q   = "SELECT CONCAT(StudyIx, FamilyIx) FROM Studies WHERE Name = '$n'";
    if($sId = $db->query($q)->fetch_row()){
      return current($sId);
    }
    die('Could not fetch the required study, sorry.');
  }
  /***/
  public static function getStudy($name){
    $db = Config::getConnection();
    $n  = $db->escape_string($name);
    $q  = "SELECT * FROM Studies WHERE Name = '$n'";
    return $db->query($q)->fetch_assoc();
  }
  /***/
  public static function getFamilies($studyId){
    $sId = Config::getConnection()->escape_string($studyId);
    $q = "SELECT * FROM Families "
       . "WHERE CONCAT(StudyIx, FamilyIx) "
       . "LIKE (SELECT CONCAT(REPLACE($sId, 0, ''), '%'))";
    return DataProvider::fetchAll($q);
  }
  /***/
  public static function getRegions($studyName){
    $db = Config::getConnection();
    $n  = $db->escape_string($studyName);
    $q  = "SELECT * FROM Regions_$n";
    return DataProvider::fetchAll($q);
  }
  /***/
  public static function getRegionLanguages($studyName){
    $db = Config::getConnection();
    $n  = $db->escape_string($studyName);
    $q  = "SELECT * FROM RegionLanguages_$n";
    return DataProvider::fetchAll($q);
  }
  /***/
  public static function getLanguages($studyName){
    $db = Config::getConnection();
    $n  = $db->escape_string($studyName);
    $q  = "SELECT * FROM Languages_$n";
    return DataProvider::fetchAll($q);
  }
  /***/
  public static function getWords($studyName){
    $db = Config::getConnection();
    $n  = $db->escape_string($studyName);
    $q  = "SELECT * FROM Words_$n";
    return DataProvider::fetchAll($q);
  }
  /***/
  public static function getMeaningGroupMembers($studyId){
    $sId = Config::getConnection()->escape_string($studyId);
    $q   = "SELECT MeaningGroupIx, MeaningGroupMemberIx, IxElicitation, IxMorphologicalInstance FROM MeaningGroupMembers "
         . "WHERE CONCAT(StudyIx, FamilyIx) = $sId";
    return DataProvider::fetchAll($q);
  }
  /***/
  public static function getTranscriptions($studyName){
    $db  = Config::getConnection();
    $n   = $db->escape_string($studyName);
    $q   = "SELECT * FROM Transcriptions_$n";
    $ret = array();
    $set = $db->query($q);
    if($set !== false){
      while($t = $set->fetch_assoc()){
        $tKey = $t['LanguageIx'].$t['IxElicitation'].$t['IxMorphologicalInstance'];
        $t['soundPaths'] = DataProvider::soundPaths($n, $t);
        if(array_key_exists($tKey, $ret)){
          //Merging transcriptions:
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
      foreach(DataProvider::getDummyTranscriptions($studyName) as $t){
        $tKey = $t['LanguageIx'].$t['IxElicitation'].$t['IxMorphologicalInstance'];
        if(!array_key_exists($tKey, $ret)){
          $ret[$tKey] = $t;
        }
      }
    }
    return $ret;
  }
  /***/
  public static function getDummyTranscriptions($studyName){
    //Add dummy transcriptions:
    $dummies = array();
    //Handling languages without transcriptions:
    $lq = "SELECT LanguageIx, FilePathPart FROM Languages_$studyName "
        . "WHERE LanguageIx NOT IN (SELECT LanguageIx FROM Transcriptions_$studyName)";
    $ls = DataProvider::fetchAll($lq);
    $pairs = array();
    if(count($ls) > 0){
      //We create pairs for all words here:
      $wq = "SELECT IxElicitation, IxMorphologicalInstance, SoundFileWordIdentifierText "
          . "FROM Words_$studyName";
      $ws = DataProvider::fetchAll($wq);
      foreach($ls as $l){
        foreach($ws as $w){
          array_push($pairs, array($l, $w));
        }
      }
    }
    //Handling words without transcriptions:
    $wq = "SELECT IxElicitation, IxMorphologicalInstance, SoundFileWordIdentifierText "
        . "FROM Words_$studyName WHERE CONCAT(IxElicitation, IxMorphologicalInstance) "
        . "NOT IN (SELECT CONCAT(IxElicitation, IxMorphologicalInstance) FROM Transcriptions_$studyName)";
    $ws = DataProvider::fetchAll($wq);
    if(count($ws) > 0){
      //We create pairs only for languages not selected above:
      $lq = "SELECT LanguageIx, FilePathPart FROM Languages_$studyName "
          . "WHERE LanguageIx IN (SELECT LanguageIx FROM Transcriptions_$studyName)";
      $ls = DataProvider::fetchAll($lq);
      foreach($ls as $l){
        foreach($ws as $w){
          array_push($pairs, array($l, $w));
        }
      }
    }
    //Handling resulting pairs:
    foreach($pairs as $p){
      $l = $p[0]; $w = $p[1];
      $files = DataProvider::findSoundFiles($studyName, $l['FilePathPart'], $w['SoundFileWordIdentifierText']);
      if(count($files) === 0) continue;
      array_push($dummies, array(
        'isDummy' => true
      , 'LanguageIx' => $l['LanguageIx']
      , 'IxElicitation' => $w['IxElicitation']
      , 'IxMorphologicalInstance' => $w['IxMorphologicalInstance']
      , 'soundPaths' => $files
      ));
    }
    return $dummies;
  }
  /***/
  public static function getDefaults($studyId){
    $db  = Config::getConnection();
    $sId = $db->escape_string($studyId);
    $ret = array();
    //Single queries:
    foreach(array(
      //Default_Languages
      'language' => "SELECT LanguageIx FROM Default_Languages "
                  . "WHERE CONCAT(StudyIx, FamilyIx) = $sId LIMIT 1"
      //Default_Words
    , 'word'     => "SELECT IxElicitation, IxMorphologicalInstance FROM Default_Words "
                  . "WHERE CONCAT(StudyIx, FamilyIx) = $sId LIMIT 1"
    ) as $k => $q){
      $ret[$k] = $db->query($q)->fetch_assoc();
    }
    //Multiple queries:
    foreach(array(
      //Default_Multiple_Languages
      'languages'  => "SELECT LanguageIx FROM Default_Multiple_Languages "
                    . "WHERE CONCAT(StudyIx, FamilyIx) = $sId"
      //Default_Multiple_Words
    , 'words'      => "SELECT IxElicitation, IxMorphologicalInstance FROM Default_Multiple_Words "
                    . "WHERE CONCAT(StudyIx, FamilyIx) = $sId"
      //Default_Languages_Exclude_Map
    , 'excludeMap' => "SELECT LanguageIx FROM Default_Languages_Exclude_Map "
                    . "WHERE CONCAT(StudyIx, FamilyIx) = $sId"
    ) as $k => $q){
      $ret[$k] = DataProvider::fetchAll($q);
    }
    return $ret;
  }
}
?>
