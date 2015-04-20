<?php
  require_once 'common.php';
  require_once 'query/translationClass.php';
  /*Login check and procedure*/
  if(!session_validate($dbConnection))
    header('LOCATION: index.php');
  if(!session_mayTranslate($dbConnection))
    header('LOCATION: index.php');
?>
<!DOCTYPE HTML>
<html>
  <?php
    $title   = "The new translation…";
    $jsFiles = array('extern/jquery.dataTables.js','dataTables.js');
    require_once 'head.php';
  ?>
  <body>
    <?php require_once 'topmenu.php';
      $action = array_key_exists('action', $_GET) ? $_GET['action'] : '';
      switch($action){
        case 'translation':
          require_once 'translation/translation.php';
        break;
        case 'search':
          require_once 'translation/search.php';
        break;
        case 'missing':
          require_once 'translation/missing.php';
        break;
        case 'changed':
          require_once 'translation/changed.php';
        break;
        default:
          require_once 'translation/overview.php';
      }
    ?>
  </body>
</html>
