<?php
require_once '../config.php';
/**
  Under http://<domain>/projects/<familyname>
  we want to serve an iframe page that seamlessly integrates
  the project URL for the given family, if possible.
  This script expects a $_GET['name'] parameter,
  and will catch the correct routes due to .htaccess magic.
*/
$family = $_GET['name'];
if($family){
  $stmt = Config::getConnection()->prepare('SELECT ProjectAboutUrl FROM Families WHERE FamilyNm = ?');
  $stmt->bind_param('s', $family);
  $stmt->execute();
  $stmt->bind_result($url);
  if($stmt->fetch()){
    echo Config::getMustache()->render('Projects', array(
      'title' => "Project page for $family"//FIXME join this with translation!
    , 'backlink' => 'soundcomparisons.com'//FIXME this one too!
    , 'url' => $url
    ));
  }else die("Sorry, we cannot find a family named '$family' in our database.");
}else die('Sorry, we cannot find that project in our database.');
