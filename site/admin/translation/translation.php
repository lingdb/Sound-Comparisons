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
      echo "<legend style='border-bottom:0px solid #ddd;'>Data to translate for $lgName:</legend>";
      if($dependsOnStudy){
        foreach($data as $study => $tdata){
          $theKeys = array_keys($tdata);
          $totalNumber = count($tdata[$theKeys[0]]) + count($tdata[$theKeys[1]]);
          $missing = 0;
          foreach($theKeys as $k){
            foreach($tdata[$k] as $r){
              if(strlen(trim($r["Original"])) > 0){
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
          echo "<h4 style='border-top:1px solid #ddd;padding-top:5px'><button onclick='toggleStudy(this, \"$study\")' style='width:24px'>+</button> Study: $study <small>- {$perDone}% done</small><br><small> missing translations: {$missing} out of {$totalNumber}</small></h4>";
          echo "<div id='$study' class='hide'>";
          showTable($tdata);
          echo "</div>";
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
