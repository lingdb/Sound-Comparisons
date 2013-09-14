<?php
  /* Setup and session verification */
  chdir('..');
  require_once 'common.php';
  session_validate($dbConnection) or die('403 Forbidden');
  session_mayEdit($dbConnection)  or die('403 Forbidden');
  /*
    Function to make tuples to insert into the db from parsed csv data
    $csv : String[][] - an array of lines which are in turn arrays of string.
    $stringFields : Int[] - Numbers of fields in each row that have to be enclosed by ''
                    $stringFields stards counting from 0.
    $deleteFields : Int[] - Indizes of fields to throw out each row.
    @return: String of complete tuples.
  */
  function mkTuples($csv, $stringFields, $deleteFields = null){
    $tuples = array(); // Tuples will be pushed in here.
    foreach($csv as $line){
      //Escaping all fields:
      foreach($line as $k => $f){
        $line[$k] = $dbConnection->escape_string($f);
      }
      //Enclosing lines:
      foreach($stringFields as $field){
        $line[$field] = "'".$line[$field]."'";
      }
      //Dealing with empty fields:
      foreach($line as $k => $field){
        if($field === '')
          $line[$k] = 'NULL';
      }
      //Deleting fields:
      if($deleteFields){
        foreach($deleteFields as $field){
          $line[$field] = false;
        }
        //Using my own filter instead of array_filter, because array_filter also filters 0.
        $nline = array();
        foreach($line as $f){
          if($f === false)
            continue;
          array_push($nline, $f);
        }
        $line = $nline;
        //$line = array_filter($line);
      }
      //Fusing lines into a tuple:
      array_push($tuples, '('.implode(",",$line).')');
    }
    return implode(",", $tuples);
  }
  //Queries to be executed for uploads:
  $queries = array();
  /* Filehandling */
  if(count($_FILES) == 0)
    die('No file given.');
  $uploads = $_FILES['upload'];
  //Iterating the files:
  while(count($uploads['name']) > 0){
    $fname    = array_pop($uploads['name']);
    $ftmpname = array_pop($uploads['tmp_name']);
    $fcontent = file_get_contents($ftmpname);
    //Parsing the csv:
    $csv = array();
    //Removing the headline and \r from $fcontent:
    if(!preg_match("/^[^\n]*\r?\n(.*)$/AsD", $fcontent, $matches))
      die('Failed to dissect file into headline and content.');
    $fcontent = preg_replace("/\r/", '', $matches[1]);
    //Handling the lines:
    foreach(preg_split("/((\r?\n)|(\r\n?))/", $fcontent) as $line){
      if($line == "")
        continue; //I don't want empty lines in $csv
      array_push($csv, str_getcsv($line));
    }
    //Selecting the queries via switch:
    switch($fname){
      case (preg_match('/^Contributors.txt$/', $fname, $matches) ? true : false):
        $q = 'INSERT INTO Contributors(ContributorIx, SortIxForAboutPage'
           . ', Forenames, Surnames, Initials, EmailUpToAt, EmailAfterAt'
           . ', PersonalWebsite, FullRoleDescription) VALUES '
           . mkTuples($csv, array(2,3,4,5,6,7,8));
        array_push($queries, 'DELETE FROM Contributors', $q);
      break;
      case (preg_match('/^DefaultSingleLanguage\.txt$/', $fname, $matches) ? true : false):
        $q = 'INSERT INTO Default_Languages(StudyIx, FamilyIx, LanguageIx) VALUES ' . mkTuples($csv, array());
        array_push($queries, 'DELETE FROM Default_Languages', $q);
      break;
      case (preg_match('/^DefaultSingleWord\.txt$/', $fname, $matches) ? true : false):
        $q = 'INSERT INTO Default_Words(StudyIx, FamilyIx, IxElicitation) VALUES ' . mkTuples($csv, array());
        array_push($queries, 'DELETE FROM Default_Words', $q, 'UPDATE Default_Words SET IxMorphologicalInstance = 0');
      break;
      case (preg_match('/^DefaultTableMultipleLanguages\.txt$/', $fname, $matches) ? true : false):
        $q = 'INSERT INTO Default_Multiple_Languages(StudyIx, FamilyIx, LanguageIx) VALUES ' . mkTuples($csv, array());
        array_push($queries, 'DELETE FROM Default_Multiple_Languages', $q);
      break;
      case (preg_match('/^DefaultTableMultipleWords\.txt$/', $fname, $matches) ? true : false):
        $q = 'INSERT INTO Default_Multiple_Words(StudyIx, FamilyIx, IxElicitation) VALUES ' . mkTuples($csv, array());
        array_push($queries, 'DELETE FROM Default_Multiple_Words', $q, 'UPDATE Default_Multiple_Words SET IxMorphologicalInstance = 0');
      break;
      case (preg_match('/^Flags\.txt$/', $fname, $matches) ? true : false):
        $q = 'INSERT INTO FlagTooltip(Flag, Tooltip) VALUES ' . mkTuples($csv, array(0,1));
        array_push($queries, "DELETE FROM FlagTooltip WHERE Flag != ''", $q);
      break;
      case (preg_match('/^Languages_(.*)\.txt$/', $fname, $matches) ? true : false):
        $q = 'INSERT INTO Languages_'.$matches[1]
           . '(StudyIx, '
           . 'FamilyIx, '
           . 'IsSpellingRfcLang, '
           . 'SpellingRfcLangName, '             // 3
           . 'AssociatedPhoneticsLgForThisSpellingLg, '
           . 'IsOrthographyHasNoTranscriptions, '
           . 'LanguageIx, '
           . 'ShortName, '                       // 7
           . 'Tooltip, '                         // 8
           . 'SpecificLanguageVarietyName, '     // 9
           . 'LanguageStatusType, '
           . 'WebsiteSubgroupName, '             // 11
           . 'WebsiteSubgroupWikipediaString, '  // 12
           . 'HistoricalPeriod, '                // 13
           . 'HistoricalPeriodWikipediaString, ' // 14
           . 'StateRegion, '                     // 15
           . 'NearestCity, '                     // 16
           . 'PreciseLocality, '                 // 17
           . 'PreciseLocalityNationalSpelling, ' // 18
           . 'ExternalWeblink, '                 // 19
           . 'FilePathPart, '                    // 20
           . 'Flag, '                            // 21
           . 'RfcLanguage, '
           . 'Latitude, '
           . 'Longtitude, '
           . 'ISOCode, '                         // 25
           . 'WikipediaLinkPart, '               // 26
           . 'ContributorSpokenBy, '
           . 'ContributorRecordedBy1, '
           . 'ContributorRecordedBy2, '
           . 'ContributorPhoneticTranscriptionBy, '
           . 'ContributorReconstructionBy, '
           . 'ContributorCitationAuthor1, '
           . 'Citation1Year, '
           . 'Citation1Pages, '                  // 34
           . 'ContributorCitationAuthor2, '
           . 'Citation2Year, '
           . 'Citation2Pages) VALUES '           // 37
           . mkTuples($csv, array(3,7,8,9,11,12,13,14,15,16,17,18,19,20,21,25,26,34,37));
        array_push($queries, 'DELETE FROM Languages_'.$matches[1], $q);
      break;
      case (preg_match('/^LanguageStatusTypes\.txt$/', $fname, $matches) ? true : false):
        $q = 'INSERT INTO LanguageStatusTypes(LanguageStatusType, Description, Status, StatusTooltip, Color) VALUES '
           . mkTuples($csv, array(1,2,3,4));
        array_push($queries, "DELETE FROM LanguageStatusTypes WHERE Description != ''", $q);
      break;
      case (preg_match('/^MeaningGroups\.txt$/', $fname, $matches) ? true : false):
        $q = 'INSERT INTO MeaningGroups(MeaningGroupIx, Name) VALUES ' . mkTuples($csv, array(1));
        array_push($queries, 'DELETE FROM MeaningGroups', $q);
      break;
      case (preg_match('/^MeaningGroupMembers\.txt$/', $fname, $matches) ? true : false):
        $q = 'INSERT IGNORE INTO MeaningGroupMembers(StudyIx, FamilyIx, MeaningGroupIx, '
           . 'MeaningGroupMemberIx, IxElicitation, IxMorphologicalInstance) '
           . 'VALUES ' . mkTuples($csv, array());
        array_push($queries, 'DELETE FROM MeaningGroupMembers', $q);
      break;
      case (preg_match('/^RegionGroupMemberLanguages_(.*)\.txt$/', $fname, $matches) ? true : false):
        $q = 'INSERT INTO RegionLanguages_'.$matches[1].'(StudyIx, FamilyIx, SubFamilyIx, '
           . 'RegionGpIx, RegionMemberLgIx, LanguageIx, RegionMemberWebsiteSubGroupIx, '
           . 'RegionGpMemberLgNameShortInThisSubFamilyWebsite, '
           . 'RegionGpMemberLgNameLongInThisSubFamilyWebsite) VALUES '
           . mkTuples($csv, array(8,9), array(6));
        array_push($queries, 'DELETE FROM RegionLanguages_'.$matches[1], $q);
      break;
      case (preg_match('/^RegionGroups_(.*)\.txt$/', $fname, $matches) ? true : false):
        $q = 'INSERT INTO Regions_'.$matches[1].'(StudyIx, FamilyIx, SubFamilyIx, RegionGpIx, '
           . 'RegionGpTypeIx, RegionGpNameLong, RegionGpNameShort) VALUES '
           . mkTuples($csv, array(5,6));
        array_push($queries, 'DELETE FROM Regions_'.$matches[1], $q);
      break;
      case (preg_match('/^Studies\.txt$/', $fname, $matches) ? true : false):
        /*
          Let this only import the studies, and not care for depending tables.
          Depending tables should be created via the interface by the user beforehand.
        */
        $q = 'INSERT INTO Studies(StudyIx, FamilyIx, SubFamilyIx, Name, '
           . 'DefaultTopLeftLat, DefaultTopLeftLon, '
           . 'DefaultBottomRightLat, DefaultBottomRightLon, ColorByFamily, SecondRfcLg) VALUES '
           . mkTuples($csv, array(3,9));
         array_push($queries, 'DELETE FROM Studies', $q);
      break;
      case (preg_match('/^Families\.txt$/', $fname, $matches) ? true : false):
        /*
          A new table Paul gave me, that links a color, a name and AbbrAllFiles to a {Study,Family}Ix.
        */
        $q = 'INSERT IGNORE INTO Families (StudyIx, FamilyIx, FamilyNm, '
           . 'FamilyAbbrAllFileNames, FamilyColorOnWebsite) VALUES '
           . mkTuples($csv, array(2,3,4));
        array_push($queries, 'DELETE FROM Families', $q);
      break;
      case (preg_match('/^Transcriptions_(.*)\.txt$/', $fname, $matches) ? true : false):
        $q = 'INSERT IGNORE INTO Transcriptions_'.$matches[1].'(StudyIx, FamilyIx, IxElicitation, '
           . 'IxMorphologicalInstance, AlternativeLexemIx, AlternativePhoneticRealisationIx, LanguageIx, '
           . 'Phonetic, SpellingAltv1, SpellingAltv2, NotCognateWithMainWordInThisFamily) VALUES '
           . mkTuples($csv, array(7,8,9));
        array_push($queries, 'DELETE FROM Transcriptions_'.$matches[1], $q);
      break;
      case (preg_match('/^Words_(.*)\.txt$/', $fname, $matches) ? true : false):
        $q = 'INSERT INTO Words_'.$matches[1].'(IxElicitation, IxMorphologicalInstance, '
           . 'MeaningGroupIx, MeaningGroupMemberIx, ThisFySortOrderByAlphabeticalOfFamilyAncestor, '
           . 'SoundFileWordIdentifierText, FileNameRfcModernLg01, FileNameRfcModernLg02, '
           . 'FileNameRfcProtoLg01, FileNameRfcProtoLg02, FullRfcModernLg01, '
           . 'FullRfcModernLg02, FullRfcProtoLg01, FullRfcProtoLg02, FullRfcProtoLg01AltvRoot, '
           . 'FullRfcProtoLg02AltvRoot) VALUES '.mkTuples($csv, array(5,6,7,8,9,10,11,12,13,14,15));
        array_push($queries, 'DELETE FROM Words_'.$matches[1], $q);
      break;
    }
  }
  //Executing queries:
  $q = 'SET AUTOCOMMIT=0;'
     . 'SET FOREIGN_KEY_CHECKS=0;'
     . implode(";",$queries).';'
     . 'SET FOREIGN_KEY_CHECKS=1;'
     . 'COMMIT;'
     . 'SET AUTOCOMMIT=1;';
//echo $q;
//file_put_contents("/tmp/fimport.debug", $q);
  $config->getConnection()->multi_query($q);
  echo "Done :)";
?>
