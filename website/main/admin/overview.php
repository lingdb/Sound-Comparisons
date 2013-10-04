<form class="form-inline" name="updatePassword" method="post" action="index.php?action=updatePassword">
  <fieldset>
    <legend>Update password:</legend>
    <label>
      <input name="current" type="password" placeholder="current password"/>
    </label>
      <input name="new" type="password" placeholder="new password"/>
    <label>
    </label>
    <label>
      <input name="confirm" type="password" placeholder="confirm password"/>
    </label>
    <button type="submit" class="btn">Update</button>
  </fieldset>
</form>
<?php if(session_mayEdit()){ ?>
  <form id="#addUser" class="form-horizontal">
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
        <button class="btn">Create</button>
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
<?php } ?>
