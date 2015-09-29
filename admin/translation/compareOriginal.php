<?php
require_once('categorySelection.php');
require_once('query/translationTableProjection.php');
if(array_key_exists('tId', $_GET)){
  $tId = intval($_GET['tId']);
}else{
  $tId = 1;
}
if(array_key_exists('tables', $_GET)){
  $tables = explode(',', $_GET['tables']);
  $projection = TranslationTableProjection::projectTables($tables);
}else{
  $projection = TranslationTableProjection::projectAll();
}
if($projection instanceof Exception){
  Config::error($projection->getMessage(), true, true);
}else{
  $changed = $projection->translationNotOriginal($tId);
  require_once('showTable.php');
  showTable(array('projection' => $changed));
}
?>
<script type="application/javascript">
<?php require_once('js/translation.js'); ?>
</script>
