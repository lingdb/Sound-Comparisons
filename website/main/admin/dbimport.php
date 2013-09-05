<?php
  require_once 'common.php';
  /*Login check and procedure*/
  if(!session_validate($dbConnection))
    header('LOCATION: index.php');
  if(!session_mayEdit($dbConnection))
    header('LOCATION: index.php');
?>
<!DOCTYPE HTML>
<html>
  <?php
    $title    = "Import of .csv files.";
    $jsFiles  = array('dbimport.js');
    require_once 'head.php';
    //<script type='application/javascript' src='js/main.js'></script>
  ?>
  <body><?php
    require_once 'topmenu.php';
    ?><form class="form-inline">
      <label>
        View the data for a LanguageFamily:
        <select name="languagefamily" id="select_languagefamily"></select>
      </label>
    </form>
    <form class="form-horizontal">
      <legend>Insert a new Languagefamily:</legend>
      <div class="control-group">
        <label class="control-label" for="studyIx">StudyIx:</label>
        <div class="controls">
          <input name="studyIx" id="text_languagefamily_studyix" type="Text">
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="familyIx">FamilyIx:</label>
        <div class="controls">
          <input name="familyIx" id="text_languagefamily_familyix" type="Text">
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="subfamilyIx">SubfamilyIx:</label>
        <div class="controls">
          <input name="subfamilyIx" id="text_languagefamily_subfamilyix" type="Text">
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="name">Name:</label>
        <div class="controls">
          <input name="name" id="text_languagefamily_name" type="Text">
        </div>
      </div>
      <div class="control-group">
        <div class="controls">
          <button id="create_languagefamily" class="btn">Create new</button>
        </div>
      </div>
    </form>
    <form class="form-horizontal" id="fileform"
      action="query/files.php" method="POST"
      enctype="multipart/form-data" target="iframe_post_form">
      <legend>Upload .csv files</legend>
      <div class="control-group">
        <label class="control-label" for="upload[]">Select files to upload:</label>
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
    <iframe name="iframe_post_form" id="iframe_post_form" class="hide"></iframe>
    <ul id="log"></ul>
  </body>
</html>
