<?php
  require_once('categorySelection.php');
  $data = Translation::getMissingTranslations($translationId);
  require_once('showTable.php');
  showTable(array($data));
?>
<script type="application/javascript">
<?php require_once('js/translation.js'); ?>
</script>
