<?php
  chdir(__DIR__);
  require_once('validate.php');
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
        echo '<li class="nav">'
           . '<a class="dropdown-toggle topLink" data-toggle="dropdown">'
           . 'Database <b class="caret"></b></a>'
           . '<ul class="dropdown-menu">'
           . '<li><a href="uploadCSV.php">Upload CSV Files</a></li>'
           . '<li><a href="uploadSQL.php">Upload and Run SQL Files (Transcriptions)</a></li>'
           . '<li class="divider"></li>'
           . '<li><a href="insertNewLg.php">Insert new Language Family</a></li>'
           . '<li class="divider"></li>'
           . '<li><a href="generateLgIndices.php">Generate Language Indices File (for Praat)</a></li>'
           . '<li class="divider"></li>'
           . '<li><a href="exportSQLDump.php">Export Database Data (SQL format)</a></li>'
           . '</ul></li>';
      ?>
      <li><a href="userAccount.php">User account</a></li>
      <?php
      if(session_mayEdit($dbConnection)){
        echo '<li class="nav">'
           . '<a class="dropdown-toggle topLink" data-toggle="dropdown">'
           . 'Diagnostics <b class="caret"></b></a>'
           . '<ul class="dropdown-menu">'
           . '<li><a href="missingSounds.php">List Missing Sounds</a></li>'
           . '<li><a href="integrity.php">DB Integrity</a></li>'
           . '<li><a href="clearCache.php">Clear Cache</a></li>'
           . '</ul></li>';
      }
      ?>
      <li><a href="index.php?action=meanings">Meanings</a></li>
      <li><a href="index.php?action=logout">Logout</a></li>
    </ul>
    <?php
      require_once('../Git.php');
      if($g = Git::getCommit('../.git')){
    ?>
    <ul class="nav pull-right" id="topMenu" data-isadmin="<?php echo $isAdmin; ?>">
      <li><a href="<?php echo $g['link']; ?>" target="_blank"><?php echo $g['text']; ?></a></li>
    </ul>
    <?php
      }
    ?>
  </div>
</div>
