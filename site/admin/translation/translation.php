<script type="application/javascript">
  function toggleStudy(btn, study) {
    $("#"+study).toggle();
    if(btn.firstChild.nodeValue==="+"){
      btn.firstChild.nodeValue = "-";
    }else{
      btn.firstChild.nodeValue = "+";
    }
  }
</script>
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
      $q = "SELECT TranslationName FROM Page_Translations WHERE TranslationId = $translationId;";
      $set = $dbConnection->query($q);
      $lgName = "";
      while($r = $set->fetch_assoc()){
        $lgName = $r["TranslationName"];
        break;
      }
      echo "<legend style='border-bottom:0px solid #ddd;'>Data to translate for $lgName - choose a study:</legend>";
      if($dependsOnStudy){
        echo "<div id='accordion'>";
        foreach($data as $study => $tdata){
          $theKeys = array_keys($tdata);
          $totalNumber = 0;
          $missing = 0;
          foreach($theKeys as $k){
            foreach($tdata[$k] as $r){
              if(strlen(trim($r["Original"])) > 0){
                $totalNumber += 1;
                if(strlen(trim($r["Translation"]["Translation"])) === 0){
                  $missing += 1;
                }
              }
            }
          }
          $perDone = 0;
          if($totalNumber>0){
            $perDone = round(($totalNumber-$missing)/$totalNumber*100,1);
          }
          echo "<div class='card' style='margin-top:5px'>";
          echo " <div class='card-header' id='headingOne$study'>";
          echo "  <button class='btn btn-large' style='width:100%;text-align:left' data-toggle='collapse' data-target='#collapseOne$study' aria-expanded='true' aria-controls='collapseOne$study'>";
          echo "   <b>$study</b><small> - {$perDone}% done</small> - <small> missing translations: {$missing} out of {$totalNumber}</small>";
          echo "  </button>";
          echo " </div>";
          echo " <div id='collapseOne$study' class='collapse show' aria-labelledby='headingOne$study' data-parent='#accordion'>";
          echo "  <div class='card-body' style='margin-top:5px'>";
          showTable($tdata);
          echo "  </div>";
          echo " </div>";
          echo "</div>";
        }
        echo "<div>";
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
