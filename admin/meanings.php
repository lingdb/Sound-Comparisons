<?php
  require_once('common.php');
  if(!session_validate($dbConnection)){
    header('LOCATION: index.php');
    die();
  }
  //Checking if some data where posted:
  $hasParams = function(){
    $params = array('name','description','example','justification');
    foreach($params as $p){
      if(!array_key_exists($p, $_POST)){
        return false;
      }
    }
    return true;
  };
  //Adding/updating on post:
  if($hasParams()){
    $q = 'INSERT INTO Meanings (name, description, example, justification) '
       . 'VALUES (?,?,?,?) '
       . 'ON DUPLICATE KEY UPDATE description=?, example=?, justification=?';
    $name = $_POST['name'];
    $description = $_POST['description'];
    $example = $_POST['example'];
    $justification = $_POST['justification'];
    $stmt = Config::getConnection()->prepare($q);
    $stmt->bind_param('sssssss', $name, $description, $example, $justification, $description, $example, $justification);
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
    <?php require_once('topmenu.php');
      if(array_key_exists('name', $_GET)){
        $q = 'SELECT name, description, example, justification FROM Meanings WHERE name = ?';
        $stmt = Config::getConnection()->prepare($q);
        $stmt->bind_param('s', $_GET['name']);
        $stmt->execute();
        $stmt->bind_result($name, $description, $example, $justification);
        if($stmt->fetch()){?>
          <form action="index.php?action=meanings" method="post">
            <fieldset>
              <legend>Editing Meaning</legend>
              <input name="name" value="<?php echo $name; ?>" placeholder="New name" type="text" required disabled>
              <label>Description:</label>
              <textarea style="width: 100%; height: 250px;" name="description" placeholder="New description" type="text" required><?php echo $description; ?></textarea>
              <label>Example:</label>
              <textarea style="width: 100%; height: 250px;" name="example" placeholder="New example" type="text" required><?php echo $example; ?></textarea>
              <label>Justification:</label>
              <textarea style="width: 100%; height: 250px;" name="justification" placeholder="New justification" type="text" required><?php echo $justification; ?></textarea>
              <button type="submit" class="btn">Save</button>
            </fieldset>
          </form>
        <?php }else{
          echo '<h1>Meaning not found :(</h1>';
        }
        $stmt->close();
      }else{
    ?>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Name:</th>
          <th>Description:</th>
          <th>Action:</th>
        </tr>
      </thead>
      <tbody><?php
        $q = 'SELECT name, description FROM Meanings';
        $meanings = DataProvider::fetchAll($q);
        foreach($meanings as $meaning){
          $name = $meaning['name'];
          $description = $meaning['description'];
          echo '<tr>'
             . "<td>$name</td>"
             . "<td>$description</td>"
             . "<td><a href='index.php?action=meanings&name=$name' class='btn'>Edit</a></td>"
             . '</tr>';
        }?>
        <tr><form action="index.php?action=meanings" method="post">
          <td><input name="name" value="" placeholder="New name" type="text" required></td>
          <td><textarea name="description" value="" placeholder="New description" type="text" required></textarea></td>
          <td><button type="submit" class="btn">Save</button></td>
          <input name="example" value="New Example" type="hidden">
          <input name="justification" value="New Justification" type="hidden">
        </form></tr>
      </tbody>
    </table><?php } /*else*/ ?>
  </body>
</html>
