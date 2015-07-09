<?php
  /**
    Implementatin of the shortLink feature.
    This file reads $_GET[] for a shortLink,
    and injects the according values into $_GET.
  */
  if(isset($_GET['shortLink'])){
    $forwarded = false;
    if($_GET['shortLink'] === 'index.php'){
      $forwarded = true;
    }else{
      $sLink = $dbConnection->escape_string($_GET['shortLink']);
      $q = "SELECT COUNT(*) FROM Page_ShortLinks WHERE Name = '$sLink'";
      if($r = $dbConnection->query($q)->fetch_row()){
        if($r[0] > 0){
          error_log($q);
          $forwarded = true;
          header('LOCATION: .#'.$sLink);
        }else{
          error_log("ShortLink not found: $sLink");
        }
      }
    }
    if(!$forwarded){
      $error = array(
        'title' => 'Shortlink not found.'
      , 'description' => "You tried to load the shortlink '$sLink'. Maybe there was a typo?"
      , 'aText' => 'Back to the main website.'
      );
      die(Config::getMustache()->render('shortlinkerror', $error));
    }
    unset($sLink, $q, $parts, $pair, $key, $val, $forwarded);
  }
