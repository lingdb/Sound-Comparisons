<?php
  /* Setup and session verification */
  chdir('..');
  require_once 'common.php';
  session_validate($dbConnection) or die('403 Forbidden');
  session_mayTranslate($dbConnection) or die('403 Forbidden');
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
      $set = mysql_query('SELECT * FROM Page_Translations ORDER BY TranslationName', $dbConnection);
      while($row = mysql_fetch_assoc($set))
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
      $translationName  = mysql_real_escape_string($_GET['TranslationName']);
      $browserMatch     = mysql_real_escape_string($_GET['BrowserMatch']);
      $imagePath        = sanitizeImagePath(mysql_real_escape_string($_GET['ImagePath']));
      $rfcLanguage      = mysql_real_escape_string($_GET['RfcLanguage']);
      $active           = mysql_real_escape_string($_GET['Active']);
      $query = "INSERT INTO Page_Translations"
        ."(TranslationName, BrowserMatch, ImagePath, RfcLanguage, Active)"
        ." VALUES ('$translationName', '$browserMatch', '$imagePath', $rfcLanguage, $active)";
      mysql_query($query, $dbConnection);
      echo mysql_insert_id($dbConnection);
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
      $translationId   = mysql_real_escape_string($_GET['TranslationId']);
      $translationName = mysql_real_escape_string($_GET['TranslationName']);
      $browserMatch    = mysql_real_escape_string($_GET['BrowserMatch']);
      $imagePath       = sanitizeImagePath(mysql_real_escape_string($_GET['ImagePath']));
      $rfcLanguage     = mysql_real_escape_string($_GET['RfcLanguage']);
      $active          = mysql_real_escape_string($_GET['Active']);
      $query = "UPDATE Page_Translations SET"
        ." TranslationName = '$translationName'"
        .", BrowserMatch = '$browserMatch'"
        .", ImagePath = '$imagePath'"
        .", RfcLanguage = $rfcLanguage"
        .", Active = $active"
        ." WHERE TranslationId = $translationId";
      mysql_query($query, $dbConnection);
    break;
    /**
      @param Req
      @param Description
    */
    case 'updateTranslationDescription':
      $req  = mysql_real_escape_string($_GET['Req']);
      $desc = mysql_real_escape_string($_GET['Description']);
      if(!session_mayEdit($dbConnection)) return;
      $q = "UPDATE Page_StaticDescription "
         . "SET Description = '$desc' WHERE Req = '$req'";
      mysql_query($q, $dbConnection);
    break;
    /**
      @param TranslationId
      @returns 'OK'|'FAIL'
    */
    case 'deletePageTranslation':
      $translationId = mysql_real_escape_string($_GET['TranslationId']);
      //Prevent deletion on default language:
      if($translationId == '1')
        die('FAIL');
      //Delete static translations:
      $query = "DELETE FROM Page_StaticTranslation WHERE TranslationId = $translationId";
      mysql_query($query, $dbConnection);
      //Delete entry itself:
      $query = "DELETE FROM Page_Translations WHERE TranslationId = $translationId";
      mysql_query($query, $dbConnection);
      echo 'OK';
    break;
    /**
      @param TranslationId source - Language from which user translates
      @param TranslationId target - Language into which user translates
      @return JSON Array of DB-entries
    */
    case 'fetchStaticTranslations':
      $source = mysql_real_escape_string($_GET['source']);
      $target = mysql_real_escape_string($_GET['target']);
      $arr = array();
      $query = "SELECT * FROM Page_StaticTranslation WHERE TranslationId = $source ORDER BY Req";
      $sourceSet = mysql_query($query, $dbConnection);
      while($sRow = mysql_fetch_assoc($sourceSet)){
        $entry = array(
          'TranslationId' => $target
        , 'Req'           => $sRow['Req']
        , 'SourceTrans'   => $sRow['Trans']
        , 'IsHtml'        => $sRow['IsHtml']
        , 'Trans'         => ''
        );
        $q = "SELECT Trans FROM Page_StaticTranslation WHERE Req = '".$sRow['Req']."' "
           . "AND TranslationId = $target";
        if($t = mysql_fetch_assoc(mysql_query($q, $dbConnection))){
          $entry['Trans'] = $t['Trans'];
        }
        $entry['Desc'] = '';
        $q = "SELECT Description FROM Page_StaticDescription WHERE Req = '".$sRow['Req']."'";
        if($d = mysql_fetch_assoc(mysql_query($q, $dbConnection)))
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
      $translationId  = mysql_real_escape_string($_GET['TranslationId']);
      $req            = mysql_real_escape_string($_GET['Req']);
      $trans          = mysql_real_escape_string(str_replace('"','\'',$_GET['Trans']));
      $isHtml         = mysql_real_escape_string($_GET['IsHtml']);
      //Delete old value:
      $query = "DELETE FROM Page_StaticTranslation WHERE TranslationId = $translationId AND Req = '$req'";
      mysql_query($query, $dbConnection);
      //Put new value:
      $query = "INSERT INTO Page_StaticTranslation VALUES ($translationId, '$req', '$trans', $isHtml)";
      mysql_query($query, $dbConnection);
      echo 'OK';
    break;
  }
?>
