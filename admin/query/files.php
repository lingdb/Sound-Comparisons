<!DOCTYPE HTML>
<html>
  <head>
    <title>Status of attempted CSV import</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="Stylesheet" type="text/css" href="../../css/extern/bootstrap.css" media="screen" />
    <link rel="Stylesheet" type="text/css" href="../css/style.css" media="screen" />
    <link rel="Stylesheet" type="text/css" href="../css/extern/jquery.dataTables.css" media="screen" />
    <script type='application/javascript' src='../../js/extern/jquery.min.js'></script>
    <script type='application/javascript' src='../../js/extern/bootstrap.js'></script>
    <script type='application/javascript' src='../../js/extern/underscore-min.js'></script>
    <script type='application/javascript' src='../../js/extern/backbone-min.js'></script>
  </head>
  <body>
  <?php
    /* Setup and session verification */
    require_once 'dbimport/Importer.php';
    chdir('..');
    require_once 'common.php';
    require_once '../query/cacheProvider.php';
    session_validate() or Config::error('403 Forbidden');
    session_mayEdit()  or Config::error('403 Forbidden');
    //Parsing client data, and using Importer:
    $uId = $dbConnection->escape_string(session_getUid());
    $merge = false;
    $fs = array();
    $uploads = $_FILES['upload'];
    if(count($uploads['name']) === 1 && $uploads['name'][0] === ''){
      Config::error('No file given.');
      echo '<h1>You need to select a file first.</h1>';
    }else{
      while(count($uploads['name']) > 0){
        array_push($fs, array(
          'name' => array_pop($uploads['name'])
        , 'path' => array_pop($uploads['tmp_name'])
        ));
      }
      CacheProvider::cleanCache('../');
      $log = Importer::processFiles($fs, $uId, $merge);
      echo '<ul><li>'.implode($log,'</li><li>').'</li></ul>';
      $tables = implode(',', Importer::findTables($fs));
      $href = "../translate.php?action=compareOriginal&tables=$tables";
      echo '<a target="_parent" href="'.$href.'" class="btn btn-primary">Review translations</a>';
    }
  ?>
  </body>
</html>
