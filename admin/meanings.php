<?php
  require_once('common.php');
  if(!session_validate($dbConnection)){
    header('LOCATION: index.php');
  }else{
  //Checking if some data where posted:
  $hasParams = function(){
    $params = array('name','description','example');
    foreach($params as $p){
      if(!array_key_exists($p, $_POST)){
        return false;
      }
    }
    return true;
  };
  //Adding/updating on post:
  if($hasParams()){
    $q = 'INSERT INTO Meanings (name, description, example) '
       . 'VALUES (?,?,?) '
       . 'ON DUPLICATE KEY UPDATE description=?, example=?';
    $name = $_POST['name'];
    $description = $_POST['description'];
    $example = $_POST['example'];
    $stmt = Config::getConnection()->prepare($q);
    $stmt->bind_param('sssss', $name, $description, $example, $description, $example);
    $stmt->execute();
    $stmt->close();
    //Redirect to make sure get rather than post will be used:
    header('LOCATION: index.php?action=meanings');
    die('');//die to make sure no content after redirect.
  }
?>
<!DOCTYPE HTML>
<html>
  <?php
    $title = "Edit and review the meanings list.";
    require_once('head.php');
  ?>
  <body>
    <?php require_once('topmenu.php'); ?>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Name:</th>
          <th>Description:</th>
          <th>Example:</th>
          <th>Action:</th>
        </tr>
      </thead>
      <tbody><?php
        $q = 'SELECT name, description, example FROM Meanings';
        $meanings = DataProvider::fetchAll($q);
        foreach($meanings as $meaning){
          $name = $meaning['name'];
          $description = $meaning['description'];
          $example = $meaning['example'];
          $mkInput = function($name, $content){
            return '<input name="'.$name.'" value="'.$content.'" type="text" required>';
          };
          $mkTextarea = function($name, $content){
            return '<textarea name="'.$name.'" required>'.$content.'</textarea>';
          };
          echo '<tr><form action="index.php?action=meanings" method="post">'
             . '<td>'.$mkInput('name',$name).'</td>'
             . '<td>'.$mkTextarea('description',$description).'</td>'
             . '<td>'.$mkTextarea('example',$example).'</td>'
             . '<td><button type="submit" class="btn">Save</button></td>'
             . "</form></tr>";
        }?>
        <tr><form action="index.php?action=meanings" method="post">
          <td><input name="name" value="" placeholder="New name" type="text" required></td>
          <td><textarea name="description" value="" placeholder="New description" type="text" required></textarea></td>
          <td><textarea name="example" value="" placeholder="New example" type="text" required></textarea></td>
          <td><button type="submit" class="btn">Save</button></td>
        </form></tr>
      </tbody>
    </table>
  </body>
</html><?php }
