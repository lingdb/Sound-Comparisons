<?php
  require_once "categorySelection.php";
  $data = Translation::getChangedTranslations($translationId);
  require_once "showTable.php";
  showTable(array($data));
?>
<script type="application/javascript">
<?php require_once "js/translation.js"; ?>
</script>
