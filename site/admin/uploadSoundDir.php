<?php
require_once('common.php');
if(!session_validate($dbConnection))
  header('LOCATION: index.php');
if(!session_mayUpload($dbConnection))
  header('LOCATION: index.php');
?>
<!DOCTYPE HTML>
<html>
  <?php
    $title = 'Upload Sound Files';
    require_once('head.php');
  ?>
  <body>
    <?php require_once('topmenu.php'); ?>
    <script>
    var filePathPart = "";
    function checkFilePartPath(p) {
      $('#uploadCheck').html("");
      if(p.files.length > 0){
        filePathPart = p.files[0]['name'];
        if(filePathPart.match(/^([A-Za-z_\d]+?)_\d{3,}_\w{1,}.*?\.(wav|ogg|mp3)/)){
          filePathPart = filePathPart.replace(/^(.+?)_\d.*/, "$1");
          $.ajax({
              type: "POST",
              url: "/admin/validateUploadFile.php",
              data: {action: 'validateUploadFile', item:filePathPart},
              dataType:'JSON', 
              success: function(response){
                var alreadyExist = response.SoundDirExists;
                alreadyExist = true;
                var r = "<table>";
                r += "<tr><td style='padding-right:20px' align='right'>Upload Request for: </i></td><td>" + response.FilePathPart + "</td></tr>";
                r += "<tr><td style='padding-right:20px' align='right'>Study:</td><td>" + response.Study + "</td></tr>";
                r += "<tr><td style='padding-right:20px' align='right'>Short Name:</td><td>" + response.ShortName + "</td></tr>";
                if(alreadyExist) {
                  r += "<tr><td style='padding-right:20px' align='right'>Sound Path exists on Server:</td><td><font color='red'>" + response.SoundDirExists + "</font></td></tr>";
                } else {
                  r += "<tr><td style='padding-right:20px' align='right'>Sound Path exists on Server:</td><td>" + response.SoundDirExists + "</td></tr>";
                }
                r += "</table>";
                if(alreadyExist){
                  r += '<div style="margin-top:20px; border-left:3px solid red">' +
                       '<p style="margin:10px"><i>What should be done due to the fact that sound files for “' +response.ShortName+ '” have already been uploaded to the server?</i></p>' +
                       '<div style="vertical-align: middle;margin:10px"><input style="margin-right: 10px;" ' +
                            'type="radio" id="bkp" name="mode" value="backup"> Backup old directory, delete old directory, and upload new sound files</div>' +
                       '<div style="vertical-align: middle;margin:10px"><input style="margin-right: 10px;" ' +
                            'type="radio" id="bkp" name="mode" value="delete"> Delete old directory (<b>without backup</b>), and upload new sound files</div>' +
                       '<div style="vertical-align: middle;margin:10px"><input style="margin-right: 10px;" ' +
                            'type="radio" id="bkp" name="mode" value="merge"> Merge (old ones will be overwritten) new sound files while uploading</div>' +
                       '</div>';

                }
                $('#uploadCheck').html(r);
              }
          });
        } else {
          $('#uploadCheck').html("It seems that the first chosen file is <b>not</b> a valid sound file name.");
        }
      }
    }
    </script>
    <div class="row-fluid" style="margin-left:20px">
      <div>
        <form id="fileform" class="form-horizontal" action="query/sql.php?action=soundupload" 
          method="POST" enctype="multipart/form-data" target="iframe_post_form">
          <legend>Upload Sound Files for <b>one</b> Language:</legend>
          <div class="control-group">
            <span>Choose <b>directory</b> <i>(containing the sound files)</i> or <b>sound files</b>:</span>&nbsp;
              <input style="vertical-align: middle; margin: 0px;" onchange="checkFilePartPath(this);" name="files[]" type="file" required multiple="" directory="" webkitdirectory="" mozdirectory=""/>
          </div>
            <div id="uploadCheck" class="span6">
            </div>
            <div class="controls">
              <button type="submit" class="btn" id="button_upload" disabled="disabled">Upload</button>
            </div>
        </form>
      </div>
    </div>
    <iframe name="iframe_post_form" id="iframe_post_form" style="border:none;"></iframe>
  </body>
</html>
