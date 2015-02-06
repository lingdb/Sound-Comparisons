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
  //Memoization for the target TranslationId
  private static $targetMemo = null;
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
      1: Negotiate the clients preferred language
      2: Fallback to default to always have a target
  */
  public static function getTarget(){
    if(self::$targetMemo === null){
      $db = Config::getConnection();
      //Phase1:
      if(array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)){
        $set = $db->query('SELECT TranslationId, BrowserMatch FROM Page_Translations WHERE Active = 1');
        while($row = $set->fetch_assoc())
          if(preg_match('/'.$row['BrowserMatch'].'/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'])){
            self::$targetMemo = $row['TranslationId'];
            return self::getTarget();
          }
      }
      //Phase2:
      self::$targetMemo = self::$defaultTranslationId;
    }
    return self::$targetMemo;
  }
  /**
    Tries to translate a request via the static table.
    @param $req String
    @param [$t = null] TranslationId
    @return $trans String
  */
  public static function staticTranslate($req, $t = null){
    if($t === null) $t = self::getTarget();
    $q = "SELECT Trans FROM Page_StaticTranslation "
       . "WHERE TranslationId = $t AND Req='$req'";
    $set = Config::getConnection()->query($q);
    if($r = $set->fetch_assoc()){
      return preg_replace('/\<br\>/', "\n", $r['Trans']);
    }
    //Fallback on default if necessary:
    if($t !== self::$defaultTranslationId){
      return self::staticTranslate($req, self::$defaultTranslationId);
    }
    //Final Fail
    return "MissingStaticTranslation($t,$req)";
  }
}
?>
