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
//Inserting/Updating of possible shortlinks:
if(array_key_exists('name', $_POST)){
  $name = $dbConnection->escape_string($_POST['name']);
  $url  = ($_POST['url']) ? $dbConnection->escape_string($_POST['url']) : '';
  if(empty($url)){
    $q = "DELETE FROM Page_ShortLinks WHERE Name = '$name'";
    $dbConnection->query($q);
  }else{
    $uid = session_getUid();
    $q = "INSERT INTO Edit_Imports (Who) VALUES ($uid)";
    $dbConnection->query($q);
    $q = "INSERT INTO Page_ShortLinks (Name, Target) "
       . "VALUES ('$name', '$url') "
       . "ON DUPLICATE KEY UPDATE Target = '$url'";
    $dbConnection->query($q);
  }
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
      <legend>Enter a name and a url to set a shortlink:</legend>
      <div class="control-group">
        <label class="control-label" for="name">Name:</label>
        <div class="controls">
          <input name="name" type="text">
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="url">Url:</label>
        <div class="controls">
          <input name="url" type="text">
        </div>
      </div>
      <div class="control-group">
        <div class="controls">
          <input class="btn" type="submit" value="Save">
        </div>
      </div>
    </form>
    <h3>Current shortlinks:</h3>
    <table class="table table-bordered">
      <thead>
        <tr><th>Name:</th><th>Url:</th></tr>
      </thead>
      <tbody><?php
        $q = "SELECT Name, Target FROM Page_ShortLinks";
        $set = $dbConnection->query($q);
        if($set->num_rows === 0){
          echo '<tr><td colspan="2">Currently no shortlinks exist.</td></tr>';
        }else{
          while($r = $set->fetch_row()){
            $name = $r[0];
            $url  = $r[1];
            echo "<tr><td>$name</td><td>$url</td></tr>";
          }
        }
      ?></tbody>
    </table>
  </body>
</html>
