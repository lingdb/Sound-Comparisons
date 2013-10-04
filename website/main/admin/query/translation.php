<?php
  /* Setup and session verification */
  chdir('..');
  require_once 'common.php';
  session_validate() or die('403 Forbidden');
  session_mayTranslate() or die('403 Forbidden');
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
      Fetches the complete Page_Translations table.
      @returns A JSON Array with JSON Objects inside.
        Fields of contained JSON Objects are named as in db.
    */
    case 'getPageTranslations':
      $arr = array(); // The JSON Array
      $set = $dbConnection->query('SELECT * FROM Page_Translations ORDER BY TranslationName');
      while($row = $set->fetch_assoc())
        array_push($arr, $row);
      echo json_encode($arr);
    break;
    /**
      @param TranslationName
      @param BrowserMatch
      @param ImagePath
      @param RfcLanguage
      @param Active
      @returns TranslationId
    */
    case 'createPageTranslation':
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
      @param TranslationName
      @param BrowserMatch
      @param ImagePath
      @param RfcLanguage
      @param Active
    */
    case 'updatePageTranslation':
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
    /**
      @param Req
      @param Description
    */
    case 'updateTranslationDescription':
      $req  = $dbConnection->escape_string($_GET['Req']);
      $desc = $dbConnection->escape_string($_GET['Description']);
      if(!session_mayEdit($dbConnection)) return;
      $q = "UPDATE Page_StaticDescription "
         . "SET Description = '$desc' WHERE Req = '$req'";
      $dbConnection->query($q);
    break;
    /**
      @param TranslationId
      @returns 'OK'|'FAIL'
    */
    case 'deletePageTranslation':
      $translationId = $dbConnection->escape_string($_GET['TranslationId']);
      //Prevent deletion on default language:
      if($translationId == '1')
        die('FAIL');
      //Delete static translations:
      $query = "DELETE FROM Page_StaticTranslation WHERE TranslationId = $translationId";
      $dbConnection->query($query);
      //Delete entry itself:
      $query = "DELETE FROM Page_Translations WHERE TranslationId = $translationId";
      $dbConnection->query($query);
      echo 'OK';
    break;
    /**
      @param TranslationId source - Language from which user translates
      @param TranslationId target - Language into which user translates
      @return JSON Array of DB-entries
    */
    case 'fetchStaticTranslations':
      $source = $dbConnection->escape_string($_GET['source']);
      $target = $dbConnection->escape_string($_GET['target']);
      $arr = array();
      $query = "SELECT * FROM Page_StaticTranslation WHERE TranslationId = $source ORDER BY Req";
      $sourceSet = $dbConnection->query($query);
      while($sRow = $sourceSet->fetch_assoc()){
        $entry = array(
          'TranslationId' => $target
        , 'Req'           => $sRow['Req']
        , 'SourceTrans'   => $sRow['Trans']
        , 'IsHtml'        => $sRow['IsHtml']
        , 'Trans'         => ''
        );
        $q = "SELECT Trans FROM Page_StaticTranslation WHERE Req = '".$sRow['Req']."' "
           . "AND TranslationId = $target";
        if($t = $dbConnection->query($q)->fetch_assoc()){
          $entry['Trans'] = $t['Trans'];
        }
        $entry['Desc'] = '';
        $q = "SELECT Description FROM Page_StaticDescription WHERE Req = '".$sRow['Req']."'";
        if($d = $dbConnection->query($q)->fetch_assoc())
          $entry['Desc'] = $d['Description'];
        array_push($arr, $entry);
      }
      echo json_encode($arr);
    break;
    /**
      @param TranslationId
      @param Req
      @param Trans
      @param IsHtml
      @return 'OK'
    */
    case 'putStaticTranslation':
      $translationId  = $dbConnection->escape_string($_GET['TranslationId']);
      $req            = $dbConnection->escape_string($_GET['Req']);
      $trans          = $dbConnection->escape_string(str_replace('"','\'',$_GET['Trans']));
      $isHtml         = $dbConnection->escape_string($_GET['IsHtml']);
      //Delete old value:
      $query = "DELETE FROM Page_StaticTranslation WHERE TranslationId = $translationId AND Req = '$req'";
      $dbConnection->query($query);
      //Put new value:
      $query = "INSERT INTO Page_StaticTranslation VALUES ($translationId, '$req', '$trans', $isHtml)";
      $dbConnection->query($query);
      echo 'OK';
    break;
  }
?>
