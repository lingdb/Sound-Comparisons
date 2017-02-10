<?php
  require_once('common.php');
  /*Login check and procedure*/
  if(!session_validate($dbConnection))
    header('LOCATION: index.php');
  if(!session_mayEdit($dbConnection))
    header('LOCATION: index.php');
?>
<!DOCTYPE HTML>
<html>
  <?php
    $title   = "Import of CSV files";
    $jsFiles = array('dbimport.js');
    require_once('head.php');
    $max = ini_get_all();
    $max = $max['max_file_uploads'];
    $max = __::min(array($max['global_value'],$max['local_value']));
  ?>
  <body><?php
    require_once('topmenu.php');
    ?>
      <form class="form-horizontal span6" id="fileform"
        action="query/files.php" method="POST"
        enctype="multipart/form-data" target="iframe_post_form">
        <legend>Upload CSV Files</legend>
        <div class="control-group">
          <label class="control-label" for="upload[]">
            Select CSV files to upload (max: <?php echo $max; ?>):
          </label>
          <div class="controls">
            <input name="upload[]" id="files" type="file" multiple/>
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="upload">Ready to upload?</label>
          <div class="controls">
            <input name="upload" id="button_upload" type="Button" value="Upload"/>
          </div>
        </div>
      </form>
    </div>
    <iframe name="iframe_post_form" id="iframe_post_form" style="border:none;"></iframe>
  </body>
</html>
