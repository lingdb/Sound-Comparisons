<?php
  require_once('categorySelection.php');
  if($providerGroup !== ''){
    $pGroups = Translation::providers();
    $dependsOnStudy = array_key_exists($providerGroup, $pGroups['_dependsOnStudy']);
    $data = array();
    if(!$dependsOnStudy){
      $data = Translation::pageAll($pGroups[$providerGroup], '', $translationId);
    }else{
      foreach(Translation::studies() as $s){
        $data[$s] = Translation::pageAll($pGroups[$providerGroup], $s, $translationId);
      }
    }
    require_once('showTable.php');
    //Below output only if $data contains something:
    if(count($data) !== 0){
      echo '<legend>Data to translate:</legend>';
      if($dependsOnStudy){
        foreach($data as $study => $tdata){
          echo "<h4>$study:</h4>";
          showTable($tdata);
        }
      }else{
        showTable($data);
      }
    }else{
      echo '<p class="well">Sorry, no data was found.</p>';
    }
  }
?>
<script type="application/javascript">
<?php require_once('js/translation.js'); ?>
</script>
