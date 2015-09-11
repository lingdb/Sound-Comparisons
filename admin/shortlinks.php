<?php
/**
  The interface to create and maintain shortlinks on the website.
*/
require_once 'common.php';
/*Login check and procedure*/
if(!session_validate($dbConnection))
  header('LOCATION: index.php');
if(!session_mayTranslate($dbConnection))
  header('LOCATION: index.php');
//Removing/Updating of possible shortlinks:
if(array_key_exists('name', $_POST)){
  $name = $_POST['name'];
  $newName = array_key_exists('newName', $_POST) ? $_POST['newName'] : '';
  if(empty($newName)){
    $q = "DELETE FROM Page_ShortLinks WHERE Name = ?";
    $stmt = $dbConnection->prepare($q);
    $stmt->bind_param('s', $name);
    $stmt->execute(); $stmt->close();
  }else{
    $uid = session_getUid();
    $q = "INSERT INTO Edit_Imports (Who) VALUES (?)";
    $stmt = $dbConnection->prepare($q);
    $stmt->bind_param('i', $uid);
    $stmt->execute(); $stmt->close();

    $q = 'UPDATE Page_ShortLinks SET Name = ? WHERE Name = ?';
    $stmt = $dbConnection->prepare($q);
    $stmt->bind_param('ss', $newName, $name);
    $stmt->execute(); $stmt->close();
  }
  unset($name, $newName);
}
?>
<!DOCTYPE HTML>
<html>
  <?php
    $title   = "Manage shortlinks for the main site.";
    $jsFiles = array();
    require_once 'head.php';
  ?>
  <body>
    <?php require_once 'topmenu.php'; ?>
    <form class="form-horizontal" method="POST" action="shortlinks.php">
      <legend>Enter a 'name' and a 'new name' to rename a shortlink:</legend>
      <div class="control-group">
        <label class="control-label" for="name">Name:</label>
        <div class="controls">
          <input name="name" type="text">
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="newName">New Name:</label>
        <div class="controls">
          <input name="url" type="text">
        </div>
      </div>
      <div class="control-group">
        <div class="controls">
          <input class="btn" type="submit" value="Update">
        </div>
      </div>
    </form>
    <h3>Current shortlinks:</h3>
    <table class="table table-bordered">
      <thead>
        <tr><th>Name:</th><th>Url:</th></tr>
      </thead>
      <tbody><?php
        $q = 'SELECT Name, Target FROM Page_ShortLinks';
        $stmt = $dbConnection->prepare($q);
        $stmt->execute();
        if($stmt->num_rows === 0){
          echo '<tr><td colspan="2">Currently no shortlinks exist.</td></tr>';
        }else{
          $stmt->bind_result($name, $url);
          while($r = $stmt->fetch()){
            echo "<tr><td>$name</td><td>$url</td></tr>";
          }
        }
        $stmt->close();
      ?></tbody>
    </table>
  </body>
</html>
