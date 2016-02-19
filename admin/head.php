<head>
  <title><?php
    if(isset($title))
      echo $title;
    else
      echo 'Beware of magic.';
  ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="Stylesheet" type="text/css" href="../css/extern/bootstrap.css" media="screen" />
  <link rel="Stylesheet" type="text/css" href="css/style.css" media="screen" />
  <link rel="Stylesheet" type="text/css" href="css/extern/jquery.dataTables.css" media="screen" />
  <script type='application/javascript' src='../js/extern/jquery.min.js'></script>
  <script type='application/javascript' src='../js/extern/bootstrap.js'></script>
  <script type='application/javascript' src='../js/extern/underscore-min.js'></script>
  <script type='application/javascript' src='../js/extern/backbone-min.js'></script>
  <?php
    if(isset($jsFiles)){
      foreach($jsFiles as $f){
        echo "<script type='application/javascript' src='js/$f'></script>";
      }
    }
  ?>
</head>
