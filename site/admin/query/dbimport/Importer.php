<?php
/*
  The Importer capsules importing .csv files into the database.
  Requires Config to be included already.
*/
class Importer{
  /**
    $log [String]
    All errors shall be pushed to the $log,
    so that we can easily find out what's wrong with some inputs.
  */
  public static $log = array();
  /**
    @param $csv_string String to parse
    @param $delimeter String to delimit fields by
    @param $skip_empty_lines Bool to drop empty lines
    @param $trim_fields Bool to drop leading/trailing whitespace
    @return [[String]]
    Black magic from http://www.php.net/manual/de/function.str-getcsv.php#111665
    This function has problems once '!!Q!!' occurs inside $csv_string,
    but in constrast to str_getcsv, it works correctly with '"' enclosed fields.
  */
  public static function parse_csv($csv_string, $delimiter = ",", $skip_empty_lines = true, $trim_fields = true){
    $enc = preg_replace('/(?<!")""/', '!!Q!!', $csv_string);
    $enc = preg_replace_callback('/"(.*?)"/s', function ($field){
      return urlencode(utf8_encode($field[1]));
    }, $enc);
    $lines = preg_split($skip_empty_lines ? ($trim_fields ? '/( *\R)+/s' : '/\R+/s') : '/\R/s', $enc);
    return array_map(function ($line) use ($delimiter, $trim_fields){
      $fields = $trim_fields ? array_map('trim', explode($delimiter, $line)) : explode($delimiter, $line);
      return array_map(function ($field){
        return str_replace('!!Q!!', '"', utf8_decode(urldecode($field)));
      }, $fields);
    }, $lines);
  }
  /**
    @param $csv String
    @return $csv ['headline' => [String], 'data' => [[String]]]
  */
  public function parse($csv){
    $csv = self::parse_csv($csv);
    $csv = array(
      'headline' => array_shift($csv)
    , 'data' => $csv // headline missing due to shift
    );
    //Checking if all data fields have the length of the headline:
    $hCount = count($csv['headline']);
    foreach($csv['data'] as $i => $data){
      $dCount = count($data);
      if($dCount < $hCount){
        $index = $i+1;
        if($dCount === 1 && current($data) === '') continue;
        array_push(self::$log, "Row $index has $dCount instead of $hCount fields. Ignoring row: ".Config::toJSON($data));
        unset($csv['data'][$i]);
      }
    }
    //Fixing indices:
    $csv['data'] = array_values($csv['data']);
    return $csv;
  }
  /**
    @param $file String filename
    @param $csv String csv data
    @param $merge Bool merge entries rather than overwrite
    @return $queries [String]
    Iff $merge is true, we shall use REPLACE INTO queries rather than DELETE followed by INSERT INTO.
  */
  public function mkQueries($file, $csv, $merge){
    array_push(self::$log, 'Importer::mkQueries for '.$file.'…');
    $desc = self::descFile($file);
    if($desc === null){
      array_push(self::$log, "Importer::mkQueries produced no queries for $file.");
      return array();
    }
    //Parsing the csv:
    $csv = self::parse($csv);
    //Finding cols to fill:
    $cols = array(); // Index -> colName || null
    $colMap = $desc['colMapping'];
    foreach($csv['headline'] as $h){
      if(array_key_exists($h, $colMap)){
        array_push($cols, $colMap[$h]);
      }else{
        array_push(self::$log, "Unknown column for file $file: '$h'");
        array_push($cols, null);//Placeholder column to ignore field
      }
    }
    array_push(self::$log, 'Columns to import: '.implode($cols,', '));
    //Tuples to insert/replace:
    $tuples = array();
    $db = Config::getConnection();
    $strMapping = self::descTable($desc['table']);
    foreach($csv['data'] as $row){
      $ts = array();
      for($i = 0; $i < count($cols); $i++){
        $c = $cols[$i];
        if($c !== null){
          if(count($row) > $i){
            $r = $db->escape_string($row[$i]);
            if(!array_key_exists($c, $strMapping)){
              array_push(self::$log, "Missing index '$c' in strMapping for table '".$desc['table']."'");
            }else if($strMapping[$c]){
              $r = "'$r'";//Encapsulate Strings
            }
            if($r === '') $r = 'NULL';//Empty Strings -> NULL
            array_push($ts, $r);
          }else{
            $i = count($cols);
            $ts = array();
          }
        }
      }
      if(count($ts) > 0){
        array_push($tuples, '('.implode($ts, ',').')');
      }
    }
    //Queries for the given $file and $csv:
    $queries = $merge ? array() : array('DELETE FROM '.$desc['table']);
    $q = ($merge ? 'REPLACE' : 'INSERT'). ' INTO '.$desc['table'].' ('.implode(__::compact($cols),',').') VALUES '.implode($tuples,',');
    array_push($queries, $q);
    return $queries;
  }
  /**
    @param $qs [String]
    @param $uId UserId for Edit_Imports table
  */
  public static function execQueries($qs, $uId){
    array_push(self::$log, "Importer::execQueries for User $uId");
    $queryFailed = false;
    $db = Config::getConnection();
    //Starting transaction:
    foreach(array(
      'SET AUTOCOMMIT=0'
    , 'SET FOREIGN_KEY_CHECKS=0'
    , "INSERT INTO Edit_Imports (Who) VALUES ($uId)"
    ) as $q) $db->query($q);
    $db->query('START TRANSACTION');
    //Executing queries:
    foreach($qs as $q){
      if(!$db->query($q)){
        array_push(self::$log, "Error with query '$q': ".$db->error);
        $queryFailed = true;
      }
    }
    // Roll back for any error
    if($queryFailed) {
      array_push(self::$log, "ALL CHANGES WERE ROLLED BACK");
      $db->query('ROLLBACK');
    } else {
      array_push(self::$log, "SUCCESS");
      $db->query('COMMIT');
    }
    //Ending transaction:
    foreach(array(
      'SET FOREIGN_KEY_CHECKS=1'
    , 'SET AUTOCOMMIT=1'
    ) as $q) $db->query($q);
  }
  /**
    @param $fs [[name => String, path => String]]
    @param $uId UserId for Edit_Importes table
    @param $merge Bool true if data should be merged/replaced rather than overwritten
    @return $log [String] of errors/warnings found by the Importer
  */
  public static function processFiles($fs, $uId, $merge){
    array_push(self::$log, "Importer::processFiles for uId $uId; merge=$merge");
    $qqs = array();
    foreach($fs as $f){
      array_push($qqs, self::mkQueries($f['name'], file_get_contents($f['path']), $merge));
    }
    self::execQueries(__::flatten($qqs), $uId);
    //Finish, cleaning the log:
    $log = self::$log;
    self::$log = array();
    return $log;
  }
  /**
    @param $table String
    @return $tDesc [Field String => IsString Bool]
    Generates a simple description of a table mapping Fields to Bools.
  */
  public static function descTable($table){
    $tDesc = array();
    $set = Config::getConnection()->query("DESCRIBE $table");
    while($r = $set->fetch_assoc()){
      $type = $r['Type'];
      $isString = ($type === 'text' || strpos($type, 'varchar') !== false);
      $tDesc[$r['Field']] = $isString;
    }
    if(count($tDesc) === 0){
      array_push(self::$log, "Importer::descTable could not find description for table $table.");
    }
    return $tDesc;
  }
  /**
    @param $file String name of a .csv file
    @return $desc [ 'table' => tableName String
                  , 'colMapping' => [headline String => colName String]
                  ] || null
    Returns the description for a fileName.
  */
  public static function descFile($file){
    foreach(self::$fileDescriptions as $regex => $fDesc){
      if(preg_match($regex, $file, $matches)){
        return array(
          'table' => ($fDesc['study']) ? $fDesc['table'].$matches[1] : $fDesc['table']
        , 'colMapping' => $fDesc['colMapping']
        );
      }
    }
    if(preg_match('/^Index_.*\.txt$/',$file)){
      return null; // This is a file that we want to ignore.
    }
    $keys = '{'.implode(', ', array_keys(self::$fileDescriptions)).'}';
    array_push(self::$log, "Importer::descFile found no match for the fileName $file."
                         , " - Make sure $file matches one of $keys.");
    return null;
  }
  /**
    @param $files [['name' => String, 'path' => String]]
    @param $cleanLog Bool=true
    @return $tables [String]
    Takes an array of descriptions for uploaded files
    and returns an array of table names.
    If $cleanLog === true, calling this method will clean self::$log.
  */
  public static function findTables($files, $cleanLog = true){
    $tables = array();
    foreach($files as $file){
      $desc = self::descFile($file['name']);
      if($desc !== null){
        array_push($tables, $desc['table']);
      }
    }
    if($cleanLog){ self::$log = array(); }
    return $tables;
  }
  /**
    [fNameRegex => ['study' => Bool, 'table' => String
                  , 'colMapping' => [headline String => colName String]
    The $fileDescriptions configure how to map files to tables.
    To do this the key entry is a regex that must match the file name for the according array value to be used.
    If the arrays study value is true, match #1 shall be appended to the table name.
    By using self::descFile, the description for a given fileName can be searched.
    Together with self::descTable it will be known, wether a field holds a string or not,
    and all values to be imported can be escaped.
  */
  private static $fileDescriptions = array(
    '/^Contributors.txt$/' => array(
      'study' => false
    , 'table' => 'Contributors'
    , 'colMapping' => array(
        'Contributor Ix' => 'ContributorIx'
      , 'Sort Group' => 'SortGroup'
      , 'Sort Ix for About page' => 'SortIxForAboutPage'
      , 'Forenames' => 'Forenames'
      , 'Surnames' => 'Surnames'
      , 'Initials' => 'Initials'
      , 'Email up to AT sign' => 'EmailUpToAt'
      , 'Email after AT sign' => 'EmailAfterAt'
      , 'Personal Website Text to link to' => 'PersonalWebsite'
      , 'Full Role Description for About page' => 'FullRoleDescription'
      )
    )
  , '/^ContributorCategories.txt$/' => array(
      'study' => false
    , 'table' => 'ContributorCategories'
    , 'colMapping' => array(
        'Sort Group' => 'SortGroup'
      , 'Heading Text for this Sort Group' => 'Headline'
      , 'Heading Abbr' => 'Abbr'
      )
    )
  , '/^DefaultSingleLanguage\.txt$/' => array(
      'study' => false
    , 'table' => 'Default_Languages'
    , 'colMapping' => array(
        'Study Ix' => 'StudyIx'
      , 'Family Ix' => 'FamilyIx'
      , 'Default Single Language Full Ix' => 'LanguageIx'
      )
    )
  , '/^DefaultLanguagesToExcludeFromMap\.txt$/' => array(
      'study' => false
    , 'table' => 'Default_Languages_Exclude_Map'
    , 'colMapping' => array(
        'Study Ix' => 'StudyIx'
      , 'Family Ix' => 'FamilyIx'
      , 'Default Language to Exclude from Map Full Ix' => 'LanguageIx'
      )
    )
  , '/^DefaultSingleWord\.txt$/' => array(
      'study' => false
    , 'table' => 'Default_Words'
    , 'colMapping' => array(
        'Study Ix' => 'StudyIx'
      , 'Family Ix' => 'FamilyIx'
      , 'Default Single Word Elicitation Ix' => 'IxElicitation'
      )
    )
  , '/^DefaultTableC..LgsForWdsXLgsTable\.txt$/' => array(
      'study' => false
    , 'table' => 'Default_Multiple_Languages_WdsXLgs'
    , 'colMapping' => array(
        'Study Ix' => 'StudyIx'
      , 'Family Ix' => 'FamilyIx'
      , 'Default Table Multiple Language Full Ix' => 'LanguageIx'
      )
    )
  , '/^DefaultTableC..LgsForLgsXWdsTable\.txt$/' => array(
      'study' => false
    , 'table' => 'Default_Multiple_Languages_LgsXWds'
    , 'colMapping' => array(
        'Study Ix' => 'StudyIx'
      , 'Family Ix' => 'FamilyIx'
      , 'Default Table Multiple Language Full Ix' => 'LanguageIx'
      )
    )
  , '/^DefaultTableC..WdsForLgsXWdsTable_(.*)\.txt$/' => array(
      'study' => true
    , 'table' => 'Default_Multiple_Words_LgsXWds_'
    , 'colMapping' => array(
        'Study Ix' => 'StudyIx'
      , 'Family Ix' => 'FamilyIx'
      , 'Ix Elicitation' => 'IxElicitation'
      )
    )
  , '/^DefaultTableC30WdsForWdsXLgsTable_(.*)\.txt$/' => array(
      'study' => true
    , 'table' => 'Default_Multiple_Words_WdsXLgs_'
    , 'colMapping' => array(
        'Study Ix' => 'StudyIx'
      , 'Family Ix' => 'FamilyIx'
      , 'Ix Elicitation' => 'IxElicitation'
      )
    )
  , '/^Flags\.txt$/' => array(
      'study' => false
    , 'table' => 'FlagTooltip'
    , 'colMapping' => array(
        'Flag Image File Name' => 'Flag'
      , 'Flag Tooltip' => 'Tooltip'
      )
    )
  , '/^Languages_(.*)\.txt$/' => array(
      'study' => true
    , 'table' => 'Languages_'
    , 'colMapping' => array(
        'Study Ix' => 'StudyIx'
      , 'Family Ix' => 'FamilyIx'
      , 'Spelling Reference Language for WebSite' => 'IsSpellingRfcLang'
      , 'Spelling Reference Language for WebSite Lg Name' => 'SpellingRfcLangName'
      , 'Associated Phonetics Lg for this Spelling Lg' => 'AssociatedPhoneticsLgForThisSpellingLg'
      , 'Is This ONLY An Orthography With No Transcriptions' => 'IsOrthographyHasNoTranscriptions'
      , 'Full Index Number' => 'LanguageIx'
      , 'Website Short Name' => 'ShortName'
      , 'Website Tooltip' => 'ToolTip'
      , 'Specific Language Variety Name' => 'SpecificLanguageVarietyName'
      , 'Language Status Type Ix' => 'LanguageStatusType'
      , 'Website Subgroup Name' => 'WebsiteSubgroupName'
      , 'Website Subgroup Wikipedia String' => 'WebsiteSubgroupWikipediaString'
      , 'Historical Period' => 'HistoricalPeriod'
      , 'Historical Period Wikipedia String' => 'HistoricalPeriodWikipediaString'
      , 'Ethnic Group' => 'EthnicGroup'
      , 'State/Region' => 'StateRegion'
      , 'Nearest City' => 'NearestCity'
      , 'Precise Locality' => 'PreciseLocality'
      , 'Precise Locality as Spelt in National Std Lg' => 'PreciseLocalityNationalSpelling'
      , 'External WebLink' => 'ExternalWeblink'
      , 'Ccd:  Overall FileName' => 'FilePathPart'
      , 'Flag' => 'Flag'
      , 'Associated Rfc Lg\'s Full Index Number' => 'RfcLanguage'
      , 'LatitudeAsString' => 'Latitude'
      , 'LongitudeAsString' => 'Longtitude'
      , 'ISO 639-3 code' => 'ISOCode'
      , 'Glottolog code' => 'GlottoCode'
      , 'Wikipedia Atv Text more precise than ISO' => 'WikipediaLinkPart'
      , 'Contributor:  Spoken By' => 'ContributorSpokenBy'
      , 'Contributor:  Recorded By 1' => 'ContributorRecordedBy1'
      , 'Contributor:  Recorded By 2' => 'ContributorRecordedBy2'
      , 'Contributor:  Sound Editing By' => 'ContributorSoundEditingBy'
      , 'Contributor:  Phonetic Transcription By' => 'ContributorPhoneticTranscriptionBy'
      , 'Contributor:  Reconstruction By' => 'ContributorReconstructionBy'
      , 'Contributor:  Citation Author 1' => 'ContributorCitationAuthor1'
      , 'Citation 1:  Year' => 'Citation1Year'
      , 'Citation 1:  Pages' => 'Citation1Pages'
      , 'Contributor:  Citation Author 2' => 'ContributorCitationAuthor2'
      , 'Citation 2:  Year' => 'Citation2Year'
      , 'Citation 2:  Pages' => 'Citation2Pages'
      , 'Speaker Surnames' => 'SpeakerSurnames'
      , 'Speaker First Names' => 'SpeakerFirstNames'
      )
)
  , '/^LanguageStatusTypes\.txt$/' => array(
      'study' => false
    , 'table' => 'LanguageStatusTypes'
    , 'colMapping' => array(
        'Language Status Type Ix' => 'LanguageStatusType'
      , 'Language Status Type Description Text' => 'Description'
      , 'Language Status Abbreviation' => 'Status'
      , 'Language Status ToolTip' => 'StatusTooltip'
      , 'Status Type Colour on Website' => 'Color'
      , 'Opacity Percentage' => 'Opacity'
      , 'Colour Depth Percentage' => 'ColorDepth'
      )
    )
  , '/^MeaningGroups\.txt$/' => array(
      'study' => false
    , 'table' => 'MeaningGroups'
    , 'colMapping' => array(
        'Meaning Group Ix' => 'MeaningGroupIx'
      , 'Meaning Group Name' => 'Name'
      )
    )
  , '/^MeaningGroupMembers\.txt$/' => array(
      'study' => false
    , 'table' => 'MeaningGroupMembers'
    , 'colMapping' => array(
        'Study Ix' => 'StudyIx'
      , 'Family Ix' => 'FamilyIx'
      , 'Meaning Group Ix' => 'MeaningGroupIx'
      , 'Meaning Group Member Ix' => 'MeaningGroupMemberIx'
      , 'Ix Elicitation' => 'IxElicitation'
      , 'Ix Morphological Instance' => 'IxMorphologicalInstance'
      )
    )
  , '/^RegionGroupMemberLanguages_(.*)\.txt$/' => array(
      'study' => true
    , 'table' => 'RegionLanguages_'
    , 'colMapping' => array(
        'Study Ix' => 'StudyIx'
      , 'Family Ix' => 'FamilyIx'
      , 'Sub-Family Ix' => 'SubFamilyIx'
      , 'Region Gp Ix' => 'RegionGpIx'
      , 'Region Member Lg Ix' => 'RegionMemberLgIx'
      , 'Region Member Lg Full Index Number' => 'LanguageIx'
      , 'Region Member Website SubGroup Ix' => 'RegionMemberWebsiteSubGroupIx'
      , 'Region Gp Member Lg Name Short In This Sub-Family WebSite' => 'RegionGpMemberLgNameShortInThisSubFamilyWebsite'
      , 'Region Gp Member Lg Name Long In This Sub-Family WebSite' => 'RegionGpMemberLgNameLongInThisSubFamilyWebsite'
      , 'Bn - Include?' => 'Include'
      )
    )
  , '/^RegionGroups_(.*)\.txt$/' => array(
      'study' => true
    , 'table' => 'Regions_'
    , 'colMapping' => array(
        'Study Ix' => 'StudyIx'
      , 'Family Ix' => 'FamilyIx'
      , 'Sub-Family Ix' => 'SubFamilyIx'
      , 'Region Gp Ix' => 'RegionGpIx'
      , 'Region Gp Type Ix' => 'RegionGpTypeIx'
      , 'Region Gp Nm Long' => 'RegionGpNameLong'
      , 'Region Gp Nm Short' => 'RegionGpNameShort'
      , 'Bn: Default Expanded State' => 'DefaultExpandedState'
      )
    )
  , '/^Studies\.txt$/' => array(
      'study' => false
    , 'table' => 'Studies'
    , 'colMapping' => array(
        'Study Ix' => 'StudyIx'
      , 'Family Ix' => 'FamilyIx'
      , 'Sub-Family Ix' => 'SubFamilyIx'
      , 'Sub-Family Nm' => 'Name'
      , 'Default Top Left Latitude' => 'DefaultTopLeftLat'
      , 'Default Top Left Longitude' => 'DefaultTopLeftLon'
      , 'Default Bottom Right Latitude' => 'DefaultBottomRightLat'
      , 'Default Bottom Right Longitude' => 'DefaultBottomRightLon'
      , 'Bn: Colour by Family Rather Than By Region' => 'ColorByFamily'
      , 'Second Rfc Lg for this Study' => 'SecondRfcLg'
      )
    )
  , '/^Families\.txt$/' => array(
      'study' => false
    , 'table' => 'Families'
    , 'colMapping' => array(
        'Study Ix' => 'StudyIx'
      , 'Family Ix' => 'FamilyIx'
      , 'Family Nm' => 'FamilyNm'
      , 'Family Abbr:  All FileNames' => 'FamilyAbbrAllFileNames'
      , 'Project About URL' => 'ProjectAboutUrl'
      , 'Bn:  Project Active?' => 'ProjectActive'
      )
    )
  , '/^Page_DynamicTranslation\.txt$/' => array(
      'study' => false
    , 'table' => 'Page_DynamicTranslation'
    , 'colMapping' => array(
        'TranslationId' => 'TranslationId'
      , 'Category' => 'Category'
      , 'Field' => 'Field'
      , 'Trans' => 'Trans'
      )
    )
  , '/^Transcription Superscript Info.txt/' => array(
      'study' => false
    , 'table' => 'TranscrSuperscriptInfo'
    , 'colMapping' => array(
        'Transcription Superscript Ix' => 'Ix'
      , 'Transcription Superscript Abbreviation' => 'Abbreviation'
      , 'Transcription Superscript Abbreviation Full Hover Text' => 'HoverText'
      )
    )
  , '/^Transcription Superscript Lender Lgs.txt/' => array(
      'study' => false
    , 'table' => 'TranscrSuperscriptLenderLgs'
    , 'colMapping' => array(
        'Lender Language ISO Code' => 'IsoCode'
      , 'Lender Language Superscript Abbreviation Letters' => 'Abbreviation'
      , 'Lender Language Full Name for Hover Text' => 'FullNameForHoverText'
      )
    )
  , '/^Transcriptions_(.*)\.txt$/' => array(
      'study' => true
    , 'table' => 'Transcriptions_'
    , 'colMapping' => array(
        'Study Ix' => 'StudyIx'
      , 'Family Ix' => 'FamilyIx'
      , 'Ix Elicitation' => 'IxElicitation'
      , 'Ix Morphological Instance' => 'IxMorphologicalInstance'
      , 'Alternative Lexeme Ix' => 'AlternativeLexemIx'
      , 'Alternative PhoneTic Realisation Ix' => 'AlternativePhoneticRealisationIx'
      , 'Full Index Number' => 'LanguageIx'
      , 'PhoneTic Transcription in Unicode' => 'Phonetic'
      , 'Spelling Altv 1' => 'SpellingAltv1'
      , 'Spelling Altv 2' => 'SpellingAltv2'
      , 'TSI 01:  Bn: Not Cognate with Main Word in This Family' => 'NotCognateWithMainWordInThisFamily'
      , 'Bn: Not Cognate with Main Word in This Family' => 'NotCognateWithMainWordInThisFamily'//TODO only seen in Mapudungun
      , 'TSI 02:  Bn: Common Root but Morpheme Structure Different' => 'CommonRootMorphemeStructDifferent'
      , 'Bn: Common Root but Morpheme Structure Different' => 'CommonRootMorphemeStructDifferent'//TODO only seen in Mapudungun
      , 'TSI 03:  Bn: Different Meaning to Usual for This Cognate' => 'DifferentMeaningToUsualForCognate'
      , 'TSI 11:  Cognate\'s Actual Meaning in This Language' => 'ActualMeaningInThisLanguage'
      , 'TSI 12:  Other Lexeme in This Language for This Meaning' => 'OtherLexemeInLanguageForMeaning'
      , 'TSI 21:  Bn: Is This Root A Loan Word from a Known Donor?' => 'RootIsLoanWordFromKnownDonor'
      , 'TSI 22:  Bn: Is This Root Shared in Another Family?' => 'RootSharedInAnotherFamily'
      , 'TSI 99:  ISO Code for Known Donor or Shared Language or Family' => 'IsoCodeKnownDonor'
      , 'TSI 04:  Txt: Different Morpheme Structure Note' => 'DifferentMorphemeStructureNote'
      , 'TSI 41:  Bn: Odd Phonology' => 'OddPhonology'
      , 'TSI 42:  Txt: Odd Phonology Note' => 'OddPhonologyNote'
      , 'TSI 13:  Txt: Usage Note' => 'UsageNote'
      , 'TSI 81:  Bn: Sound Problem' => 'SoundProblem'
      , 'TSI 05:  Bn: Reconstructed or Historical Form Questionable?' => 'ReconstructedOrHistQuestionable'
      , 'TSI 06:  Txt: Reconstructed or Historical Form Questionable Note' => 'ReconstructedOrHistQuestionableNote'
      )
    )
  , '/^Words_(.*)\.txt$/' => array(
      'study' => true
    , 'table' => 'Words_'
    , 'colMapping' => array(
        'Ix Elicitation' => 'IxElicitation'
      , 'Ix Morphological Instance' => 'IxMorphologicalInstance'
      , 'Meaning Group Ix' => 'MeaningGroupIx'
      , 'Meaning Group Member Ix' => 'MeaningGroupMemberIx'
      , 'This Fy:  Sort Order By Alphabetical of Family Ancestor' => 'ThisFySortOrderByAlphabeticalOfFamilyAncestor'
      , 'Ccd:  for Sound Files:  Word Identifier Text' => 'SoundFileWordIdentifierText'
      , 'For FileName:  Rfc Modern Lg:  01' => 'FileNameRfcModernLg01'
      , 'For FileName:  Rfc Modern Lg:  02' => 'FileNameRfcModernLg02'
      , 'For FileName:  Rfc Proto-Lg:  01' => 'FileNameRfcProtoLg01'
      , 'For FileName:  Rfc Proto-Lg:  02' => 'FileNameRfcProtoLg02'
      , 'Full Written Form:  Rfc Modern Lg:  01' => 'FullRfcModernLg01'
      , 'Longer Written Form if Needed:  Rfc Modern Lg:  01' => 'LongerRfcModernLg01'
      , 'Full Written Form:  Rfc Modern Lg:  02' => 'FullRfcModernLg02'
      , 'Longer Written Form if Needed:  Rfc Modern Lg:  02' => 'LongerRfcModernLg02'
      , 'Full Written Form:  Rfc Proto-Lg:  01' => 'FullRfcProtoLg01'
      , 'Full Written Form:  Rfc Proto-Lg:  02' => 'FullRfcProtoLg02'
      , 'Full Written Form:  Rfc Proto-Lg:  01  Altv Root' => 'FullRfcProtoLg01AltvRoot'
      , 'Full Written Form:  Rfc Proto-Lg:  02  Altv Root' => 'FullRfcProtoLg02AltvRoot')
    )
  , '/^Book1.csv$/' => array(//Added for #240, given by Paul.
      'study' => false
    , 'table' => 'Meanings'
    , 'colMapping' => array(
        'Entry Rfc Lg 1' => 'name'
      , 'Ix Elicitation' => 'IxElicitation'
      )
    )
  );
}
