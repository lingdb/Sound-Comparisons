<?php
/**
  Integrity assumes that Config and DataProvider are available.
  It checks the database for expected constraints.
  Build to deal with https://github.com/sndcomp/website/issues/66
*/
class Integrity {
  /**
    returns the combined result of check{Pk,Fk,NotValues}.
  */
  public static function checkIntegrity(){
    return array(
      'pk' => static::checkPk()
    , 'fk' => static::checkFk()
    , 'notValues' => static::checkNotValues()
    );
  }
  /**
    @return [TableName => [json => JsonRow, reason => String]]
    Checks all pk entries of Integrity::$constraints.
    TableName is expected to be a complete name, not a prefix.
  */
  public static function checkPk(){
    $ret = array();
    foreach(self::$constraints as $tPrefix => $tDesc){
      if(array_key_exists('pk', $tDesc)){
        $key = 'CONCAT('.implode(',', $tDesc['pk']).')';
        foreach(self::getTableNames($tPrefix, $tDesc) as $table){
          $q1 = "SELECT COUNT(*) FROM $table";
          $q2 = "SELECT COUNT(DISTINCT $key) FROM $table";
          $r1 = current(current(DataProvider::fetchAll($q1)));
          $r2 = current(current(DataProvider::fetchAll($q2)));
          if($r1 !== $r2){
            //http://forums.mysql.com/read.php?10,171410,171412#msg-171412
            $q = "SELECT $key FROM $table GROUP BY $key HAVING COUNT($key) > 1";
            $jsons = array();
            foreach(DataProvider::fetchAll($q) as $row){
              array_push($jsons, array('json'=> Config::toJSON($row), 'reason' => 'Primary key violated.'));
            }
            $ret[$table] = $jsons;
          }
        }
      }
    }
    return $ret;
  }
  /**
    @return [TableName => [json => JsonRow, reason => String]]
    Checks all fk entries of Integrity::$constraints.
    TableName is expected to be a complete name, not a prefix.
  */
  public static function checkFk(){
    $ret = array();
    foreach(self::$constraints as $tPrefix => $tDesc){
      if(array_key_exists('fk', $tDesc)){
        foreach($tDesc['fk'] as $fk){
          $lKey = 'CONCAT('.implode(',',$fk['key']).')';
          $rKey = 'CONCAT('.implode(',',$fk['ref']).')';
          foreach(self::getTableNames($tPrefix, $tDesc) as $table){
            $tQs = array();
            foreach(self::getTableNamesFor($table, $fk['table']) as $t){
              array_push($tQs, "SELECT $rKey FROM $t");
            }
            $q = "SELECT * FROM $table WHERE $lKey NOT IN (".implode(' UNION ',$tQs).')';
            $rs = DataProvider::fetchAll($q);
            if(count($rs) > 0){
              $jsons = array();
              foreach($rs as $r){
                array_push($jsons, array('json' => Config::toJSON($r), 'reason' => 'Foreign key violated for '.$fk['table']));
              }
              $ret[$table] = $jsons;
            }
          }
        }
      }
    }
    return $ret;
  }
  /**
    @return [TableName => [json => JsonRow, reason => String]]
    Checks all notValues entries of Integrity::$constraints.
    TableName is expected to be a complete name, not a prefix.
  */
  public static function checkNotValues(){
    $ret = array();
    foreach(self::$constraints as $tPrefix => $tDesc){
      if(array_key_exists('notValues', $tDesc)){
        $or = array();
        foreach($tDesc['notValues'] as $k => $v){
          array_push($or, "$k = $v");
        }
        $or = implode(' OR ', $or);
        foreach(self::getTableNames($tPrefix, $tDesc) as $table){
          $q = "SELECT * FROM $table WHERE $or";
          $rs = DataProvider::fetchAll($q);
          if(count($rs) > 0){
            $jsons = array();
            foreach($rs as $r){
              array_push($jsons, array('json' => Config::toJSON($r), 'reason' => 'Forbidden combination of key/value occured.'));
            }
            $ret[$table] = $jsons;
          }
        }
      }
    }
    return $ret;
  }
  /**
    @param $key TableName | TablePrefix
    @param $val element of array_values(self::$constraints)
    @return $ret [TableName]
    Takes a key=>value pair from self::$constraints, and produces an array of TableNames.
  */
  public static function getTableNames($key, $val){
    if(array_key_exists('perStudy',$val)){
      if($val['perStudy'] === true){
        $ret = array();
        foreach(DataProvider::getStudies() as $s){
          array_push($ret, $key.$s);
        }
        return $ret;
      }
    }
    return array($key);
  }
  /**
    @param $table TableName
    @param $target TableName | TablePrefix
    @return $tNames [TableName]
    1: If $target doesn't depend on study, its the TableName
    2: If $table depends on study, $target should get the same
    3: If $table doesn't depend, $target should be done for all studies.
  */
  public static function getTableNamesFor($table, $target){
    $tNames = self::getTableNames($target, self::$constraints[$target]);
    if(count($tNames) === 1) return $tNames;
    //https://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
    $endsWith = function($haystack, $needle){
      if($needle === '') return true;
      $t = strlen($haystack) - strlen($needle);
      if($t < 0) return false;
      return (strpos($haystack, $needle, $t) !== false);
    };
    //Searching possible study suffix:
    foreach(DataProvider::getStudies() as $s){
      if($endsWith($table, $s)){//Searching the only one:
        foreach($tNames as $t){
          if($endsWith($t, $s)){
            return array($t);
          }
        }
      }
    }
    return $tNames;
  }
  /**
    Array tree that describes different constraints on database tables.
    [TableName => [
      perStudy => Boolean // If existent and true, TableName is only a TablePrefix that needs a Study as Suffix.
    , pk => [ColNames] // If existent, the concat of cols is supposed to be a primary key for the table.
    , fk => [
        table => TableName // Entry of the ArrayTree.
      , key => [ColNames] // concat of cols that are supposed to be local representation of foreign key.
      , ref => [ColNames] // concat of cols that are supposed to be referenced representation of foreign key.
      ]
    , notValues => [ // The or of the below combinations is not allowed.
        ColName => Value // The and of the combination of ColName+Value is not allowed.
      ]
    ]]
  */
  public static $constraints = array(
    'FlagTooltip' => array(
      'pk' => array('Flag')
    )
  , 'LanguageStatusTypes' => array(
      'pk' => array('LanguageStatusType')
    )
  , 'Studies' => array(
      'pk' => array('StudyIx', 'FamilyIx', 'SubFamilyIx')
    )
  , 'Families' => array(
      'pk' => array('StudyIx', 'FamilyIx', 'FamilyNm')
    )
  , 'Edit_Users' => array(/* Nothing */)
  , 'Edit_Imports' => array(
      'fk' => array(
        array('table' => 'Edit_Users', 'key' => array('Who'), 'ref' => array('UserId'))
      )
    )
  , 'Export_Soundfiles' => array(
      'pk' => array('FileName') 
    )
  , 'Page_Translations' => array(/* Nothing */)
  , 'Page_ShortLinks' => array(
      'pk' => array('Name')
    )
  , 'Page_StaticDescription' => array(
      'pk' => array('Req')
    )
  , 'Page_StaticTranslation' => array(
      'fk' => array(
        array('table' => 'Page_Translations', 'key' => array('TranslationId'), 'ref' => array('TranslationId'))
      , array('table' => 'Page_StaticDescription', 'key' => array('Req'), 'ref' => array('Req'))
      )
    )
  , 'Page_DynamicTranslation' => array(
      'pk' => array('TranslationId', 'Category','Field') 
    , 'fk' => array(
        array('table' => 'Page_Translations', 'key' => array('TranslationId'), 'ref' => array('TranslationId'))
      )
    )
  , 'Default_Words' => array(
      'fk' => array(
        array('table' => 'Words_', 'key' => array('IxElicitation', 'IxMorphologicalInstance', 'StudyIx', 'FamilyIx'), 'ref' => array('IxElicitation', 'IxMorphologicalInstance', 'StudyIx', 'FamilyIx'))
      //, array('table' => 'Studies', 'key' => array('StudyIx', 'FamilyIx'), 'ref' => array('StudyIx', 'FamilyIx'))//Disabled bc of how Studies works.
      )
    )
  , 'Default_Languages' => array(
      'pk' => array('LanguageIx', 'StudyIx', 'FamilyIx')
    , 'fk' => array(
        array('table' => 'Languages_', 'key' => array('LanguageIx'), 'ref' => array('LanguageIx'))
      //, array('table' => 'Studies', 'key' => array('StudyIx', 'FamilyIx'), 'ref' => array('StudyIx', 'FamilyIx'))//Disabled bc of how Studies works.
      )
    )
  , 'Default_Multiple_Words' => array(
      'fk' => array(
        array('table' => 'Words_', 'key' => array('IxElicitation', 'IxMorphologicalInstance', 'StudyIx', 'FamilyIx'), 'ref' => array('IxElicitation', 'IxMorphologicalInstance', 'StudyIx', 'FamilyIx'))
      //, array('table' => 'Studies', 'key' => array('StudyIx', 'FamilyIx'), 'ref' => array('StudyIx', 'FamilyIx'))//Disabled bc of how Studies works.
      )
    )
  , 'Default_Multiple_Languages' => array(
      'pk' => array('LanguageIx', 'StudyIx', 'FamilyIx')
    , 'fk' => array(
        array('table' => 'Languages_', 'key' => array('LanguageIx'), 'ref' => array('LanguageIx'))
      //, array('table' => 'Studies', 'key' => array('StudyIx', 'FamilyIx'), 'ref' => array('StudyIx', 'FamilyIx'))//Disabled bc of how Studies works.
      )
    )
  , 'Default_Languages_Exclude_Map' => array(
      'pk' => array('LanguageIx', 'StudyIx', 'FamilyIx')
    )
  , 'MeaningGroups' => array(
      'pk' => array('MeaningGroupIx')
    )
  , 'MeaningGroupMembers' => array(
      'pk' => array('MeaningGroupIx', 'StudyIx', 'FamilyIx', 'IxElicitation', 'IxMorphologicalInstance')
    , 'fk' => array(
        array('table' => 'MeaningGroups', 'key' => array('MeaningGroupIx'), 'ref' => array('MeaningGroupIx'))
      //, array('table' => 'Studies', 'key' => array('StudyIx', 'FamilyIx'), 'ref' => array('StudyIx', 'FamilyIx'))//Disabled bc of how Studies works.
      )
    )
  , 'WikipediaLinks' => array(
      'pk' => array('BrowserMatch', 'ISOCode', 'WikipediaLinkPart')
    )
  , 'Contributors' => array(
      'pk' => array('ContributorIx')
    )
  , 'TranscrSuperscriptInfo' => array(
      'pk' => array('Ix')
    )
  , 'TranscrSuperscriptLenderLgs' => array(
      'pk' => array('IsoCode')
    )
  , 'Regions_' => array(
      'pk' => array('StudyIx', 'FamilyIx', 'SubFamilyIx', 'RegionGpIx')
    , 'fk' => array(
        //array('table' => 'Studies', 'key' => array('StudyIx', 'FamilyIx', 'SubFamilyIx'), 'ref' => array('StudyIx', 'FamilyIx', 'SubFamilyIx'))//Disabled bc of how Studies works.
      )
    , 'perStudy' => true
    )
  , 'Languages_' => array(
      'pk' => array('LanguageIx')
    , 'fk' => array(
        array('table' => 'LanguageStatusTypes', 'key' => array('LanguageStatusType'), 'ref' => array('LanguageStatusType'))
      , array('table' => 'FlagTooltip', 'key' => array('Flag'), 'ref' => array('Flag'))
      , array('table' => 'Contributors', 'key' => array('ContributorSpokenBy'), 'ref' => array('ContributorIx'))
      , array('table' => 'Contributors', 'key' => array('ContributorRecordedBy1'), 'ref' => array('ContributorIx'))
      , array('table' => 'Contributors', 'key' => array('ContributorRecordedBy2'), 'ref' => array('ContributorIx'))
      , array('table' => 'Contributors', 'key' => array('ContributorSoundEditingBy'), 'ref' => array('ContributorIx'))
      , array('table' => 'Contributors', 'key' => array('ContributorPhoneticTranscriptionBy'), 'ref' => array('ContributorIx'))
      , array('table' => 'Contributors', 'key' => array('ContributorReconstructionBy'), 'ref' => array('ContributorIx'))
      , array('table' => 'Contributors', 'key' => array('ContributorCitationAuthor1'), 'ref' => array('ContributorIx'))
      , array('table' => 'Contributors', 'key' => array('ContributorCitationAuthor2'), 'ref' => array('ContributorIx'))
      , array('table' => 'RegionLanguages_', 'key' => array('LanguageIx'), 'ref' => array('LanguageIx'))
      )
    , 'perStudy' => true
    , 'notValues' => array(
        'Latitude' => 0
      , 'Longtitude' => 0
      )
    )
  , 'RegionLanguages_' => array(
      'pk' => array('StudyIx', 'FamilyIx', 'SubFamilyIx', 'RegionGpIx', 'LanguageIx')
    , 'fk' => array(
        array('table' => 'Regions_', 'key' => array('StudyIx', 'FamilyIx', 'SubFamilyIx', 'RegionGpIx'), 'ref' => array('StudyIx', 'FamilyIx', 'SubFamilyIx', 'RegionGpIx'))
      , array('table' => 'Languages_', 'key' => array('LanguageIx'), 'ref' => array('LanguageIx'))
      )
    , 'perStudy' => true
    )
  , 'Words_' => array(
      'pk' => array('IxElicitation', 'IxMorphologicalInstance')
    , 'perStudy' => true
    )
  , 'Transcriptions_' => array(
      'pk' => array('StudyIx', 'FamilyIx', 'IxElicitation', 'IxMorphologicalInstance', 'AlternativePhoneticRealisationIx', 'AlternativeLexemIx', 'LanguageIx')
    , 'fk' => array(
        array('table' => 'Words_', 'key' => array('IxElicitation', 'IxMorphologicalInstance'), 'ref' => array('IxElicitation', 'IxMorphologicalInstance'))
      , array('table' => 'Languages_', 'key' => array('LanguageIx'), 'ref' => array('LanguageIx'))
      //, array('table' => 'Studies', 'key' => array('StudyIx', 'FamilyIx'), 'ref' => array('StudyIx', 'FamilyIx'))//Disabled bc of how Studies works.
      )
    , 'perStudy' => true
    )
  );
}
