<?php
/**
  This is mostly a helper class for query/translations.php.
  Since some of it's parts shall be used in other places,
  it makes sense to outsource the raw information methods here.
  TranslationProvider assumes that config.php has been included
  before operations take place.
  TranslationProvider will also replace some methods of TranslationManager,
  which is part of the ValueManager I'd like to get rid of.
*/
class TranslationProvider {
  /***/
  public static $defaultTranslationId = 1;
  /**
    @param $tId TranslationId
  */
  public static function getDynamic($tId){
    $dbConnection = Config::getConnection();
    $tId = $dbConnection->escape_string($tId);
    $q   = "SELECT Category, Field, Trans FROM Page_DynamicTranslation WHERE TranslationId = $tId";
    $set = $dbConnection->query($q);
    $ret = array();
    //FIXME use DataProvider::fetchAll
    while($r = $set->fetch_assoc()){
      array_push($ret, $r);
    }
    return $ret;
  }
  /**
    @param $tId TranslationId
  */
  public static function getStatic($tId){
    $dbConnection = Config::getConnection();
    $tId = $dbConnection->escape_string($tId);
    $q   = "SELECT Req, Trans FROM Page_StaticTranslation WHERE TranslationId = $tId";
    $set = $dbConnection->query($q);
    $ret = array();
    //FIXME use DataProvider::fetchAll
    while($r = $set->fetch_assoc()){
      $ret[$r['Req']] = $r['Trans'];
    }
    return $ret;
  }
  /***/
  public static function getSummary(){
    $dbConnection = Config::getConnection();
    $q = 'SELECT TranslationId, TranslationName, BrowserMatch, ImagePath, '
       . 'RfcLanguage, UNIX_TIMESTAMP(lastChangeStatic), UNIX_TIMESTAMP(lastChangeDynamic) '
       . 'FROM Page_Translations WHERE Active = 1 OR TranslationId = 1';
    $set   = $dbConnection->query($q);
    $ret   = array();
    $trans = self::getTarget();
    //FIXME use DataProvider::fetchAll
    while($r = $set->fetch_assoc()){
      $ret[$r['TranslationId']] = array(
        'TranslationId'     => $r['TranslationId']
      , 'TranslationName'   => $r['TranslationName']
      , 'BrowserMatch'      => $r['BrowserMatch']
      , 'ImagePath'         => $r['ImagePath']
      , 'RfcLanguage'       => $r['RfcLanguage']
      , 'lastChangeStatic'  => $r['UNIX_TIMESTAMP(lastChangeStatic)']
      , 'lastChangeDynamic' => $r['UNIX_TIMESTAMP(lastChangeDynamic)']
      );
    }
    return $ret;
  }
  /**
    Returns the autodetected TranslationId for the current client.
    Decision is taken as follows:
      1: Is there already info in $_GET?
      2: Negotiate the clients preferred language
      3: Fallback to default to allways have a target
  */
  public static function getTarget(){
    $db = Config::getConnection();
    //Phase 1:
    if(isset($_GET['hl'])){ // hl as in host language.
      $hl = $db->escape_string($_GET['hl']);
      $q = "SELECT TranslationId FROM Page_Translations WHERE BrowserMatch = '$hl'";
      if($r = $db->query($q)->fetch_row()){
        return $r[0];
      }
    }
    //Phase 2:
    if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
      $set = $db->query('SELECT TranslationId, BrowserMatch FROM Page_Translations WHERE Active = 1');
      while($row = $set->fetch_assoc())
        if(preg_match('/'.$row['BrowserMatch'].'/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'])){
          return $row['TranslationId'];
        }
    }
    //Phase 3:
    return self::$defaultTranslationId;
  }
}
?>
