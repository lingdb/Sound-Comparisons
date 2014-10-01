<?php
  require_once 'common.php';
  if(!session_validate($dbConnection))
    header('LOCATION: index.php');
?>
<!DOCTYPE HTML>
<html>
  <?php
    $title   = "Welcome to the administration area.";
    $jsFiles = array("overview.js");
    require 'head.php';
  ?>
  <body>
    <?php
      require 'topmenu.php';
    ?>
    <form class="form-inline" name="updatePassword" method="post" action="index.php?action=updatePassword">
      <fieldset>
        <legend>Update password:</legend>
        <label>
          <input name="new" type="password" placeholder="new password"/>
        </label>
        <label>
          <input name="confirm" type="password" placeholder="confirm password"/>
        </label>
        <button type="submit" class="btn">Update</button>
      </fieldset>
    </form>
    <?php if(session_mayEdit()){ ?>
      <form id="addUser" class="form-horizontal">
        <legend>Add user:</legend>
        <div class="control-group">
          <label class="control-label" for="username">Username:</label>
          <div class="controls">
            <input name="username" type="text" placeholder="Username"/>
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="password">Password:</label>
          <div class="controls">
            <input name="password" type="text" placeholder="visible password"/>
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="mayTranslate">Can translate:</label>
          <div class="controls">
            <input name="mayTranslate" type="checkbox" checked="checked"/>
          </div>
        </div>
        <div class="control-group">
          <label class="control-label" for="mayEdit">Is admin:</label>
          <div class="controls">
            <input name="mayEdit" type="checkbox"/>
          </div>
        </div>
        <div class="control-group">
          <div class="controls">
            <button class="btn" id="createUser">Create</button>
          </div>
        </div>
      </form>
      <legend>Current users:</legend>
      <table id="userEditTable" class="table">
        <thead>
          <tr>
            <th>UserId</th>
            <th>Login</th>
            <th>Password</th>
            <th>Translate</th>
            <th>Admin</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody><?php
          require 'userEditTable.php';
      ?></tbody>
      </table>
      <div class="row-fluid">
        <div class="span6">
          <legend>Export/Import users:</legend>
          <form class="form-horizontal">
            <div class="control-group">
              <label class="control-label" for="export">Download users.json file:</label>
              <div class="controls">
                <a class="btn" href="query/admin.php?action=export">Export</a>
              </div>
            </div>
          </form>
          <form class="form-horizontal" action="query/admin.php?action=import" method="post" enctype="multipart/form-data">
            <div class="control-group">
              <label class="control-label" for="import">users.json file to upload:</label>
              <div class="controls">
                <input name="import" type="file" required/>
              </div>
            </div>
            <div class="control-group">
              <label class="control-label">Ready for import?</label>
              <div class="controls">
                <button type="submit" class="btn">Import</button>
              </div>
            </div>
          </form>
        </div>
        <div class="span6">
          <legend>Export/Import translations:</legend>
          <form class="form-horizontal">
            <div class="control-group">
              <label class="control-label" for="export">Download translations file:</label>
              <div class="controls">
                <a class="btn" href="query/export.php">Export</a>
              </div>
            </div>
          </form>
          <form class="form-horizontal" action="query/import.php" method="post" enctype="multipart/form-data">
            <div class="control-group">
              <label class="control-label" for="import">Translations file to upload:</label>
              <div class="controls">
                <input name="import" type="file" required/>
              </div>
            </div>
            <div class="control-group">
              <label class="control-label">Ready for import?</label>
              <div class="controls">
                <button type="submit" class="btn">Import</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    <?php } ?>
  </body>
</html>
