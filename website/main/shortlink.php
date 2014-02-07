<?php
  /**
    Implementatin of the shortLink feature.
    This file reads $_GET[] for a shortLink,
    and injects the according values into $_GET.
  */
  if(isset($_GET['shortLink'])){
    $sLink = $dbConnection->real_escape_string($_GET['shortLink']);
    $q = "SELECT Target FROM Page_ShortLinks WHERE Name = '$sLink'";
    if($r = $dbConnection->query($q)->fetch_row()){
      $q = parse_url($r[0], PHP_URL_QUERY);
      $parts = explode('&', $q);
      foreach($parts as $p){
        $pair = explode('=', $p);
        if(count($pair) === 2){
          $key = $pair[0];
          $val = urldecode($pair[1]);
          $_GET[$key] = $val;
        }
      }
    }
    unset($sLink, $q, $parts, $pair, $key, $val);
  }
?>
