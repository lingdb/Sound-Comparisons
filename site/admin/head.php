<head>
  <title><?php
    if(isset($title))
      echo $title;
    else
      echo 'Beware of magic.';
  ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <!-- For IE 9 and below. ICO should be 32x32 pixels in size -->
  <!--[if IE]><link rel="shortcut icon" href="../img/favicon_admin.ico"><![endif]-->
  <link rel="icon" href="../img/favicon_admin.png" type="image/png" sizes="16x16">
  <link rel="icon" href="../img/favicon_admin.gif" type="image/gif" sizes="16x16">
  <link rel="Stylesheet" type="text/css" href="../css/extern/bootstrap.css" media="screen" />
  <link rel="Stylesheet" type="text/css" href="css/style.css" media="screen" />
  <link rel="Stylesheet" type="text/css" href="css/extern/jquery.dataTables.css" media="screen" />
  <script type='application/javascript' src='../js/bower_components/jquery/dist/jquery.min.js'></script>
  <script type='application/javascript' src='../js/extern/bootstrap.js'></script>
  <script type='application/javascript' src='../js/bower_components/underscore/underscore-min.js'></script>
  <script type='application/javascript' src='../js/bower_components/backbone/backbone-min.js'></script>
  <?php
    if(isset($jsFiles)){
      foreach($jsFiles as $f){
        echo "<script type='application/javascript' src='js/$f'></script>";
      }
    }
  ?>
</head>
