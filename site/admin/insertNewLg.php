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
    $title   = "Insert new Language Family";
    $jsFiles = array('dbimport.js');
    require_once('head.php');
  ?>
  <body><?php
    require_once('topmenu.php');
    ?>
    <div  style="margin-left:20px">
      <form class="form-inline">
        <label>
          View the data for a Language Family:
          <select name="languagefamily" id="select_languagefamily"></select>
        </label>
      </form>
      <div class="row-fluid">
        <form class="form-horizontal span6">
          <legend>Insert a new Language Family:</legend>
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
              <button id="create_languagefamily" class="btn">Create New</button>
            </div>
          </div>
        </form>
      </div>
    </div>
    <iframe name="iframe_post_form" id="iframe_post_form" style="border:none;"></iframe>
  </body>
</html>
