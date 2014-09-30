<?php
  require_once 'validate.php';
  if(session_mayEdit($dbConnection))
    $isAdmin    = '1';
  else $isAdmin = '0';
?><div class="navbar">
  <div class="navbar-inner">
    <ul class="nav" id="topMenu" data-isadmin="<?php echo $isAdmin; ?>">
      <li><a href="index.php">Overview</a></li><?php
      if(session_mayTranslate($dbConnection))
        echo '<li><a href="translate.php">Translate</a></li>';
      if(session_mayEdit($dbConnection))
        echo '<li><a href="dbimport.php">Import</a></li>';
      if(session_mayTranslate($dbConnection))
        echo '<li><a href="shortlinks.php">Shortlinks</a></li>';
      if(session_validate($dbConnection))
        echo '<li><a href="index.php?action=logout">Logout</a></li>';
    ?></ul>
  </div>
</div>
