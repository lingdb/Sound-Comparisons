<?php
  require_once 'validate.php';
  if(session_mayEdit($dbConnection))
    $isAdmin    = '1';
  else $isAdmin = '0';
?><div class="navbar">
  <div class="navbar-inner">
    <ul class="nav" id="topMenu" data-isadmin="<?php echo $isAdmin; ?>">
      <?php
      if(session_mayTranslate($dbConnection))
        echo '<li><a href="translate.php">Translate</a></li>';
      if(session_mayEdit($dbConnection))
        echo '<li><a href="dbimport.php">Import</a></li>';
      if(session_mayTranslate($dbConnection))
        echo '<li><a href="shortlinks.php">Shortlinks</a></li>';
      ?>
      <li><a href="userAccount.php">User account</a></li>
      <?php
      if(session_mayEdit($dbConnection)){
        echo '<li><a href="sqlFrontend.php">SQL operations</a></li>'
           . '<li><a href="missingSounds.php">Missing sounds</a></li>';
      }
      ?>
      <li><a href="index.php?action=logout">Logout</a></li>
    </ul>
    <?php
      require_once 'git.php';
      if($g = git_info()){
    ?>
    <ul class="nav pull-right" id="topMenu" data-isadmin="<?php echo $isAdmin; ?>">
      <li><a href="<?php echo $g['link']; ?>" target="_blank"><?php echo $g['text']; ?></a></li>
    </ul>
    <?php
      }
    ?>
  </div>
</div>
