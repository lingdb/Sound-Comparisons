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
    $title = 'Generate an empty CSV template for uploading Page_DynamicTranslation';
    $jsFiles = array('dbimport.js');
    require_once('head.php');
  ?>
  <body>
    <?php require_once('topmenu.php'); ?>
    <div class="row-fluid" style="margin-left:20px">
      <div class="span8">
        <form id="fileform" class="form-horizontal" action="query/sql.php?action=genPageDynTrans" 
          method="POST" target="iframe_post_form">
              <legend>Generate and Download an empty CSV template for uploading Page_DynamicTranslation data</legend>
              <p><i>Within the templates the English translation will be shown</i></p>
              <p>Please choose <b>first</b> a translation language and then press a study to download the file:</p>
              <select name="transLg">
                <?php
                foreach(DataProvider::getAllTranslationLanguages() as $s){
                  $lgid = $s['TranslationId'];
                  $lg = $s['TranslationName'];
                  echo "<option value='$lgid'>$lg</option>";
                }
                ?>
              </select><br /><br />
              <div class="btn-group">
              <?php
                // I do not why the first button doesn't work if one deletes the following line @TODO
                echo "<button type='submit' class='btn' id='button_upload' name='study' value='' style='display:none'></button>";
                foreach(DataProvider::getStudies() as $s){
                  echo "<button type='submit' class='btn' id='button_upload' name='study' value='$s'>$s</button>";
                }
              ?>
              </div>
        </form>
      </div>
    </div>
    <iframe name="iframe_post_form" id="iframe_post_form" style="border:none;"></iframe>
  </body>
</html>
