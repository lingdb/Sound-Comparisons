<?php
  /* FIXME comments are outdated and will need rewriting. */
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
  /**
    Translation by Search has prooved very effective,
    and I've decided that I'd like to use it for the
    other parts of translation aswell.
    To aid this decision, translation_common.php
    sets up the $providers to be used by search.php
    aswell as other parts that will follow.
  */
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
  require_once 'providers/WordsTranslationProvider.php';
  chdir('..');
  require_once 'common.php';
  session_validate()     or Config::error('403 Forbidden');
  session_mayTranslate() or Config::error('403 Forbidden');
  /* Providers: */
  $providers = array();
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
  , new WordsTranslationProvider('Trans_FullRfcModernLg01',   $dbConnection)
  , new WordsTranslationProvider('Trans_LongerRfcModernLg01', $dbConnection)
  ) as $p) $providers[$p->getName()] = $p;
  /**
    @param $imagePath String
    @return $imagePath String
    Removes leading '../' from the $imagePath.
  */
  function sanitizeImagePath($imagePath){
    return preg_replace('/^(\.\.\/)*/', '', $imagePath);
  }
  //Actions:
  switch($_GET['action']){
    /**
      @param TranslationName
      @param BrowserMatch
      @param ImagePath
      @param RfcLanguage
      @param Active
      @returns TranslationId
    */
    case 'createTranslation':
      $translationName = $dbConnection->escape_string($_GET['TranslationName']);
      $browserMatch    = $dbConnection->escape_string($_GET['BrowserMatch']);
      $imagePath       = sanitizeImagePath($dbConnection->escape_string($_GET['ImagePath']));
      $rfcLanguage     = $dbConnection->escape_string($_GET['RfcLanguage']);
      $active          = $dbConnection->escape_string($_GET['Active']);
      $query = "INSERT INTO Page_Translations"
        ."(TranslationName, BrowserMatch, ImagePath, RfcLanguage, Active)"
        ." VALUES ('$translationName', '$browserMatch', '$imagePath', $rfcLanguage, $active)";
      $dbConnection->query($query);
      echo $dbConnection->insert_id();
    break;
    /**
      @param TranslationId
      @returns 'OK'|'FAIL'
    */
    case 'deleteTranslation':
      $translationId = $dbConnection->escape_string($_GET['TranslationId']);
      //Prevent deletion on default language:
      if($translationId == '1')
        Config::error('FAIL: Cannot delete Translation 1.');
      //Delete translation in all providers:
      $ps = array(); // deleteTranslation will only be executed once per class.
      foreach($providers as $p)
        $ps[get_class($p)] = $p;
      foreach($ps as $p)
        $p->deleteTranslation($translationId);
      //Delete entry itself:
      $query = "DELETE FROM Page_Translations WHERE TranslationId = $translationId";
      $dbConnection->query($query);
      echo 'OK';
    break;
    /**
      @param $_GET['Providers'] JSON array of strings
      @param $_GET['Study'] String of the study to use
      @param $_GET['TranslationId'] The TranslationId to use
      Delivers a JSON object that maps names of providers to their offsets.
    */
    case 'offsets':
      $ps = json_decode($_GET['Providers']);
      $study = $dbConnection->escape_string($_GET['Study']);
      $tId = $dbConnection->escape_string($_GET['TranslationId']);
      $ret = array();
      foreach($ps as $p)
        $ret[$p] = $providers[$p]->offsets($tId, $study);
      echo json_encode($ret);
    break;
    /**
      @param $_GET['Providers'] JSON array of strings
      @param $_GET['Study'] String of the study to use
      @param $_GET['TranslationId'] The TranslationId to use
      @param $_GET['Offset'] The offset to use
      Delivers a JSON object that maps names of providers to their pages.
    */
    case 'page':
      $ps = json_decode($_GET['Providers']);
      $study = $dbConnection->escape_string($_GET['Study']);
      $tId = $dbConnection->escape_string($_GET['TranslationId']);
      $offset = $dbConnection->escape_string($_GET['Offset']);
      $ret = array();
      foreach($ps as $p){
        $ret[$p] = $providers[$p]->page($tId, $study, $offset);
      }
      echo json_encode($ret);
    break;
    /**
      Builds a mapping of ProviderGroups to Provider Names
      and outputs this as a JSON Object.
    */
    case 'providers':
      $providerGroups = array(
        'Families'            => '/^FamilyTranslationProvider$/'
      , 'Languages'           => '/^LanguagesTranslationProvider-/'
      , 'LanguageStatusTypes' => '/^LanguageStatusTypesTranslationProvider-/'
      , 'MeaningGroups'       => '/^MeaningGroupsTranslationProvider$/'
      , 'RegionLanguages'     => '/^RegionLanguagesTranslationProvider-/'
      , 'Regions'             => '/^RegionsTranslationProvider-/'
      , 'Static'              => '/^StaticTranslationProvider$/'
      , 'Studies'             => '/^StudyTranslationProvider$/'
      , 'StudyTitle'          => '/^StudyTitleTranslationProvider$/'
      , 'Words'               => '/^WordsTranslationProvider-/'
      );
      $ret = array();
      foreach($providerGroups as $group => $regex)
        $ret[$group] = __(array_keys($providers))->filter(function($k) use ($regex){
          return preg_match($regex, $k);
        });
      echo json_encode($ret);
    break;
    /**
      @param $_GET['TranslationId'] TranslationId to search for
      @param $_GET['SearchText'] Text to search for
      Delivers matches as produced by all providers.
    */
    case 'search':
      $translationId = $dbConnection->escape_string($_GET['TranslationId']);
      $searchText = $dbConnection->escape_string($_GET['SearchText']);
      $matches = array();
      foreach($providers as $p){
        $ms = $p->search($translationId, $searchText);
        $matches = array_merge($matches, $ms);
      }
      echo json_encode($matches);
    break;
    /** Returns a JSON array of all Names in the Studies table. */
    case 'studies':
      $data = array();
      $q    = "SELECT DISTINCT Name FROM Studies";
      $set  = $dbConnection->query($q);
      while($r = $set->fetch_row()){
        array_push($data, $r[0]);
      }
      echo json_encode($data);
    break;
    /**
      Fetches the complete Page_Translations table.
      @returns A JSON Array with JSON Objects inside.
        Fields of contained JSON Objects are named as in db.
    */
    case 'translations':
      $arr = array(); // The JSON Array
      $set = $dbConnection->query('SELECT * FROM Page_Translations ORDER BY TranslationName');
      while($row = $set->fetch_assoc())
        array_push($arr, $row);
      echo json_encode($arr);
    break;
    /**
      @param $_GET['TranslationId']
      @param $_GET['Payload'] The payload that determines what will be updated.
      @param $_GET['Update'] The update value to write
      @param $_GET['Provider'] The Provider to perform the update to
    */
    case 'update':
      $translationId = $dbConnection->escape_string($_GET['TranslationId']);
      $payload = $_GET['Payload'];
      $update = $_GET['Update'];
      $provider = $_GET['TranslationProvider'];
      if(array_key_exists($provider, $providers)){
        $p = $providers[$provider];
        $p->update($translationId, $payload, $update);
      }else Config::error("Unsupported Provider: $provider");
    break;
    /**
      @param Req
      @param Description
    */
    case 'updateDescription':
      $req  = $dbConnection->escape_string($_GET['Req']);
      $desc = $dbConnection->escape_string($_GET['Description']);
      if(!session_mayEdit($dbConnection)) return;
      $q = "UPDATE Page_StaticDescription "
         . "SET Description = '$desc' WHERE Req = '$req'";
      $dbConnection->query($q);
    break;
    /**
      @param TranslationId
      @param TranslationName
      @param BrowserMatch
      @param ImagePath
      @param RfcLanguage
      @param Active
    */
    case 'updateTranslation':
      $translationId   = $dbConnection->escape_string($_GET['TranslationId']);
      $translationName = $dbConnection->escape_string($_GET['TranslationName']);
      $browserMatch    = $dbConnection->escape_string($_GET['BrowserMatch']);
      $imagePath       = sanitizeImagePath($dbConnection->escape_string($_GET['ImagePath']));
      $rfcLanguage     = $dbConnection->escape_string($_GET['RfcLanguage']);
      $active          = $dbConnection->escape_string($_GET['Active']);
      $query = "UPDATE Page_Translations SET"
        ." TranslationName = '$translationName'"
        .", BrowserMatch = '$browserMatch'"
        .", ImagePath = '$imagePath'"
        .", RfcLanguage = $rfcLanguage"
        .", Active = $active"
        ." WHERE TranslationId = $translationId";
      $dbConnection->query($query);
    break;
  }
?>
