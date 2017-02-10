<?php
require_once('common.php');
if(!session_validate($dbConnection))
  header('LOCATION: index.php');
if(!session_mayEdit($dbConnection))
  header('LOCATION: index.php');
?>
<!DOCTYPE HTML>
<html>
  <?php
    $title = 'Generate Language Indices File (for Praat)';
    $jsFiles = array('dbimport.js');
    require_once('head.php');
  ?>
  <body>
    <?php require_once('topmenu.php'); ?>
    <div class="row-fluid" style="margin-left:20px">
      <div class="span8">
        <form id="fileform" class="form-horizontal" action="query/sql.php?action=genLgIdx" 
          method="POST" target="iframe_post_form">
              <legend>Generate and Download the Language Indices File (for Praat)</legend> 
              <p>By pressing the <b>Generate &amp; Download</b> button the file <code>lg_idx_filename.txt</code> used by the Praat script <code>extractWAVfiles.praat</code> (both are hosted on owncloud) will be generated and downloaded.</legend></p>
              <button type="submit" class="btn" id="button_upload">Generate &amp; Download</button>
        </form>
      </div>
    </div>
    <iframe name="iframe_post_form" id="iframe_post_form" style="border:none;"></iframe>
  </body>
</html>
