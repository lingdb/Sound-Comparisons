<?php
  require_once "categorySelection.php";
  $sText = $_GET['SearchText'];
  $sAll  = array_key_exists('SearchAll', $_GET);
  $data  = Translation::search($translationId, $sText, $sAll);
  //var_dump($data);
  if(count($data) > 0){
    require_once "showTable.php";
    showTable(array($data));
  }else{
    echo '<p class="well">Sorry, nothing was found.</p>';
  }
?>
<script type="application/javascript">
<?php require_once "js/translation.js"; ?>
</script>
