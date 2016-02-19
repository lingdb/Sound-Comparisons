<?php
  /*
    This script harvests wikipediaLinks and stores them in the database.
    Links are searched for every ISO code and every Translation in the db.
  */
  require_once('../config.php');
  $dbConnection = Config::getConnection();
  /*
    We need the ISOCodes from all studies,
    so we first need all studies:
  */
  $studies = array();
  $set = $dbConnection->query('SELECT Name FROM Studies');
  while($r = $set->fetch_row())
    array_push($studies, $r[0]);
  //Looking for ISOCodes and LinkParts:
  $targets = array();
  foreach($studies as $study){
    $q = "SELECT ISOCode, WikipediaLinkPart FROM Languages_$study WHERE ISOCODE != ''";
    $set = $dbConnection->query($q);
    while($r = $set->fetch_row())
      $targets[implode(',',$r)] = $r;
  }
  echo "Targets loaded:\t".count($targets)."\n";
  /*
    They are originally used to determine the browser language,
    but for now I'll use them also to figure out the wikipedia language
    necessary for that translation.
    The BrowserMatches are used as regexes to macht against the
    Accept-Language header anyway, and can therefore be short.
  */
  $langs = array();
  $set = $dbConnection->query('SELECT DISTINCT BrowserMatch FROM Page_Translations');
  while($r = $set->fetch_row())
    array_push($langs, $r[0]);
  echo "Translations loaded:\t".count($langs)."\n";
  /*Now we can iterate all targets to find fetch their pages in the english wikipedia:*/
  foreach($targets as $t){
    $url = ($t[1] === '') ? "http://en.wikipedia.org/wiki/ISO_639:".$t[0]
                          : "http://en.wikipedia.org/wiki/".$t[1];
    echo "Fetching $url\n";
    $page = file_get_contents($url);
    foreach($langs as $l){
      $preg = "/<li.*interwiki-$l\".*href=\"([^\"]*)\".*<\/li>/";
      preg_match($preg, $page, $matches);
      if(count($matches)){
        $href = 'http:'.$dbConnection->escape_string($matches[1]);
        echo "Found match for iso='".$t[0]."', part='".$t[1]."', lang='$l':\t$href\n";
      }else{
        $href = $url;
      }
      $q = "INSERT INTO WikipediaLinks(BrowserMatch, ISOCode, WikipediaLinkPart, Href) "
         . "VALUES ('$l','".$t[0]."','".$t[1]."','$href') "
         . "ON DUPLICATE KEY UPDATE Href = '$href'";
      $dbConnection->query($q);
    }
  }
