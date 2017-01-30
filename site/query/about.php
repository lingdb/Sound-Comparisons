<?php
require_once('../config.php');
/**
  We expect a page parameter to be given.
*/
if(array_key_exists('page', $_GET)){
  $page = urlencode($_GET['page']);
  $project = 'Sound-Comparisons';
  $user = 'lingdb';
  $url = "https://raw.githubusercontent.com/wiki/$user/$project/$page.md";
  $markdown = file_get_contents($url);
  echo $markdown;
}else{
  die('Please provide a `page` parameter.');
}
