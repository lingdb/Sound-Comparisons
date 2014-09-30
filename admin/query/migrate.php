<?php
  /*
    A simple script triggering the migration of translations for the new schema/providers.
  */
  if(php_sapi_name() !== 'cli'){
    die('This script can only be executed locally.');
  }
  require_once 'providers/TranslationProvider.php';
  require_once 'providers/StaticTranslationProvider.php';
  require_once 'providers/FamilyTranslationProvider.php';
  require_once 'providers/DynamicTranslationProvider.php';
  require_once 'providers/LanguageStatusTypesTranslationProvider.php';
  require_once 'providers/LanguagesTranslationProvider.php';
  require_once 'providers/MeaningGroupsTranslationProvider.php';
  require_once 'providers/RegionLanguagesTranslationProvider.php';
  require_once 'providers/RegionsTranslationProvider.php';
  require_once 'providers/StudyTranslationProvider.php';
  require_once 'providers/StudyTitleTranslationProvider.php';
  require_once 'providers/TranscrSuperscriptInfoTranslationProvider.php';
  require_once 'providers/TranscrSuperscriptLenderLgsTranslationProvider.php';
  require_once 'providers/WordsTranslationProvider.php';
  chdir('..');
  require_once 'common.php';
  /* Providers: */
  foreach(array(
    new StaticTranslationProvider($dbConnection)
  , new FamilyTranslationProvider($dbConnection)
  , new LanguageStatusTypesTranslationProvider('Trans_Status',        $dbConnection)
  , new LanguageStatusTypesTranslationProvider('Trans_Description',   $dbConnection)
  , new LanguageStatusTypesTranslationProvider('Trans_StatusTooltip', $dbConnection)
  , new LanguagesTranslationProvider('Trans_ShortName',                   $dbConnection)
  , new LanguagesTranslationProvider('Trans_SpellingRfcLangName',         $dbConnection)
  , new LanguagesTranslationProvider('Trans_SpecificLanguageVarietyName', $dbConnection)
  , new MeaningGroupsTranslationProvider($dbConnection)
  , new RegionLanguagesTranslationProvider('Trans_RegionGpMemberLgNameShortInThisSubFamilyWebsite', $dbConnection)
  , new RegionLanguagesTranslationProvider('Trans_RegionGpMemberLgNameLongInThisSubFamilyWebsite',  $dbConnection)
  , new RegionsTranslationProvider('Trans_RegionGpNameShort', $dbConnection)
  , new RegionsTranslationProvider('Trans_RegionGpNameLong',  $dbConnection)
  , new StudyTranslationProvider($dbConnection)
  , new StudyTitleTranslationProvider($dbConnection)
  , new TranscrSuperscriptInfoTranslationProvider('Trans_Abbreviation',              $dbConnection)
  , new TranscrSuperscriptInfoTranslationProvider('Trans_HoverText',                 $dbConnection)
  , new TranscrSuperscriptLenderLgsTranslationProvider('Trans_Abbreviation',         $dbConnection)
  , new TranscrSuperscriptLenderLgsTranslationProvider('Trans_FullNameForHoverText', $dbConnection)
  , new WordsTranslationProvider('Trans_FullRfcModernLg01',   $dbConnection)
  , new WordsTranslationProvider('Trans_LongerRfcModernLg01', $dbConnection)
  ) as $p){
    $n = $p->getName();
    echo "Migrating $n..\t";
    $p->migrate();
    echo "OK\n";
  }
  echo "Cleaning empty entries…\n";
  $dbConnection->query("DELETE FROM Page_DynamicTranslation WHERE Trans = ''");
  echo "Done .)\n";
?>
