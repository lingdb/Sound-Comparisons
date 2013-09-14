<?php
  /**
    Translation by search was built to aid entering
    and correcting Translations.
    It works in the following way:
    1.: A query is searched for,
      which generates a set of results that match
      the given input (via contains).
      The query consists of a searchtext and a
      target translation.
    2.: The client can edit any of the results
      and submit updates for them.
  */
  /* Setup and session verification */
  require_once 'search/SearchProvider.php';
  require_once 'search/StaticSearchProvider.php';
  require_once 'search/FamilySearchProvider.php';
  require_once 'search/DynamicSearchProvider.php';
  require_once 'search/LanguageStatusTypesSearchProvider.php';
  require_once 'search/LanguagesSearchProvider.php';
  require_once 'search/MeaningGroupsSearchProvider.php';
  require_once 'search/RegionLanguagesSearchProvider.php';
  require_once 'search/RegionsSearchProvider.php';
  require_once 'search/StudySearchProvider.php';
  require_once 'search/StudyTitleSearchProvider.php';
  require_once 'search/WordsSearchProvider.php';
  chdir('..');
  require_once 'common.php';
  session_validate($dbConnection)     or die('403 Forbidden');
  session_mayTranslate($dbConnection) or die('403 Forbidden');
  /* Providers: */
  $providers = array();
  foreach(array(
    new StaticSearchProvider($dbConnection)
  , new FamilySearchProvider($dbConnection)
  , new LanguageStatusTypesSearchProvider('Trans_Status',        $dbConnection)
  , new LanguageStatusTypesSearchProvider('Trans_Description',   $dbConnection)
  , new LanguageStatusTypesSearchProvider('Trans_StatusTooltip', $dbConnection)
  , new LanguagesSearchProvider('Trans_ShortName',                   $dbConnection)
  , new LanguagesSearchProvider('Trans_SpellingRfcLangName',         $dbConnection)
  , new LanguagesSearchProvider('Trans_SpecificLanguageVarietyName', $dbConnection)
  , new MeaningGroupsSearchProvider($dbConnection)
  , new RegionLanguagesSearchProvider('Trans_RegionGpMemberLgNameShortInThisSubFamilyWebsite', $dbConnection)
  , new RegionLanguagesSearchProvider('Trans_RegionGpMemberLgNameLongInThisSubFamilyWebsite',  $dbConnection)
  , new RegionsSearchProvider('Trans_RegionGpNameShort', $dbConnection)
  , new RegionsSearchProvider('Trans_RegionGpNameLong',  $dbConnection)
  , new StudySearchProvider($dbConnection)
  , new StudyTitleSearchProvider($dbConnection)
  , new WordsSearchProvider($dbConnection)
  ) as $p) $providers[$p->getName()] = $p;
  /* Action handling: */
  switch($_GET['action']){
    case 'search':
      $translationId = $dbConnection->escape_string($_GET['TranslationId']);
      $searchText    = $dbConnection->escape_string($_GET['SearchText']);
      $matches = array();
      foreach($providers as $p){
        $ms = $p->search($translationId, $searchText);
        $matches = array_merge($matches, $ms);
      }
      echo json_encode($matches);
    break;
    case 'update':
      $translationId = $dbConnection->escape_string($_GET['TranslationId']);
      $payload = $_GET['Payload'];
      $update = $_GET['Update'];
      $searchProvider = $_GET['SearchProvider'];
      if(array_key_exists($searchProvider, $providers)){
        $p = $providers[$searchProvider];
        $p->update($translationId, $payload, $update);
      }else die("Unsupported SearchProvider: $searchProvider");
    break;
    default:
      echo "Undefined query; Action needs to be [search|update].";
  }
?>
