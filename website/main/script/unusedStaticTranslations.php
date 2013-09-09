<?php
  /**
    This script reads the req for all static translations
    from the db, and tries to find them as an infix in any
    .php file under main.
    It prints out the req that can't be found,
    which is a hint that they might be removed
    from the database.
  */
  require_once '../config.php';
  $dbConnection = $config->getConnection();
  $reqs = array();
  $set  = mysql_query('SELECT Req FROM Page_StaticDescription', $dbConnection);
  while($r = mysql_fetch_row($set))
    array_push($reqs, $r[0]);
  exec('find .. -type f -regex .*php', $files);
  $contents = '';
  foreach($files as $f)
    $contents .= file_get_contents($f);
  foreach($reqs as $req){
    $found = preg_match("/$req/", $contents);
    if(!$found)
      echo "Could not find req: $req\n";
  }
?>
