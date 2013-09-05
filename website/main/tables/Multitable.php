<?php
/*
  Echoes a multiwordtable for a given valueManager.
*/
function tables_multiwordTable($v){
  $t = $v->getTranslator();
  $words = $v->getWords();
  //Precalculating mappings for MeaningGroups:
  if($v->gwo()->isLogical()){
    //We need to calculate a colspan for each MeaningGroup:
    $mgc = array(); // MeaningGroup->getId() => colspan
    $mgs = array(); // MeaningGroup->getId() => MeaningGroup
    foreach($v->getWords() as $w){
      foreach($w->getMeaningGroups() as $m){
        $mId = $m->getId();
        if(!array_key_exists($mId, $mgc))
          $mgc[$mId] = 0;
        $mgc[$mId] = $mgc[$mId] + 1;
        $mgs[$mId] = $m;
      }
    }
  }
  //thead is composed out of three rows: the MeaningGroups, The delete all + Words, and the plays:
  echo '<table id="multitable" class="table table-bordered table-striped"><thead>';
  //The MeaningGroups:
  if($v->gwo()->isLogical()){
    echo '<tr><th></th><th></th><th></th>';
    foreach($mgs as $mId => $mg){
      $name = $mg->getName();
      $span = $mgc[$mId];
      echo "<th class='mg' colspan='$span'>$name</th>";
    }
    echo '</tr>';
  }
  //Delete All:
  $deleteAll = $t->st('tabulator_multi_clear_all');
  $whref     = $v->setWords()->setUserCleaned()->link();
  $wclean    = $t->st('tabulator_multi_clear_words');
  $rowspan   = $v->gwo()->isLogical() ? 2 : 1;
  echo "<tr><th></th><th>$deleteAll "
     . "<a $whref class='color-word'>$wclean<i class='icon-arrow-right'></i></a></th><th></th>";
  //The Words:
  $wc = 0;
  while(($w = array_shift($words)) || $wc <3){//Oh look, a heart♥
    if($w){
      $dhref = $v->delWord($w)->setUserCleaned()->link();
      $dttip = $t->st('tabulator_multi_tooltip_removeWord');
      $href  = $v->gpv()->setSingleView()->setLanguages()->setWord($w)->link();
      $ttip  = $t->st('tabulator_multi_wordonly');
      $ptip  = $t->st('tabulator_multi_playword');
      $trans = $w->getTranslation($v, true, false);
      $maps  = $w->getMapsLink($t);
      echo "<th class='removeParent'>"
         . "<a $dhref title='$dttip' class='remove'><i class='icon-remove-custom'></i></a>"
         . "<a class='tableLink color-word' $href title='$ttip'>$trans</a>"
         . "</th>";
    }else{
      echo "<th class='color-word'>".$t->st('tabulator_multi_wordcol').' '.($wc+1).'</th>';
    }
    $wc++;
  }
  echo '</tr>';
  //Delete all Languages:
  $lhref  = $v->setLanguages()->setUserCleaned()->link();
  $lclean = $t->st('tabulator_multi_clear_languages');
  echo "<tr><th></th><th>$deleteAll "
     . "<a $lhref class='color-language'>$lclean<i class='icon-arrow-down'></i></a></th>";
  //Transpose:
  $trTtip = $t->st('tabulator_multi_transpose');
  $trHref = $v->gpv()->transpose()->link();
  echo "<th><a $trHref><img src='img/transpose.png' title='$trTtip' /></a></th>";
  //The Plays:
  $wc    = 0;
  $words = $v->getWords();
  while(($w = array_shift($words)) || $wc <3){//Oh look, a heart♥
    if($w){
      $ptip  = $t->st('tabulator_multi_playword');
      $maps  = $w->getMapsLink($t);
      echo "<th class='icons'>$maps"
         . "<i class='icon-eject rotate180 multitablePlayNs' title='$ptip'></i>"
         . "</th>";
    }else echo '<th></th>';
    $wc++;
  }
  //thead finished.
  echo '</thead><tbody>';
  //To build the table body, we first need all regions:
  $rc        = array(); // Region->getId() => rowspan
  $regions   = array(); // Region->getId() => Region
  $languages = $v->getLanguages();
  foreach($languages as $l){
    $r    = $l->getRegion();
    if($r === null)
      die("tables/Multitable.php: can't find a Region for LanguageIx: ".$l->getId());
    $rId  = $r->getId();
    if(!array_key_exists($rId, $rc)){
      $rc[$rId] = 0;
    }
    $rc[$rId]      = $rc[$rId] + 1;
    $regions[$rId] = $r;
  }
  //Got all the regions, putting the languages:
  $lc = 0;
  $clearRow   = "<tr><td colspan='".($wc + 3)."' class='spaceRow'></td></tr>";
  $lastFamily = -1;
  while(($l = array_shift($languages)) || $lc < 3){
    //In case this row starts with a region:
    if($l && $r = $regions[$l->getRegion()->getId()]){
      //Class of the regionrow:
      $rowClass = "regionNormal";
      //Handling of clearRows depending on the regionType:
      $fId = $r->getFamily()->getId();
      if($fId != $lastFamily){
        $lastFamily = $fId;
        echo $clearRow;
      }
      //The Regionrow begins:
      echo "<tr>";
      //Typical output for the region:
      $rId           = $r->getId();
      $regions[$rId] = null;
      $rspan         = $rc[$rId];
      $name          = $r->getShortName();
      $rColor        = $r->getColorStyle();
      echo "<td class='regionCell color-region' rowspan='$rspan'$rColor>$name</td>";
    }else{ // Cases where the row doesn't start with a sregion:
      echo '<tr>';
    }
    //Displaying the language:
    if($l){
      $lhref = $v->delLanguage($l)->setUserCleaned()->link();
      $lttip = $t->st('tabulator_multi_tooltip_removeLanguage');
      $ttip  = $t->st('tabulator_multi_langonly');
      $ptip  = $t->st('tabulator_multi_playlang');
      $href  = $v->gpv()->setLanguageView()->setWords()->setLanguage($l)->link();
      $sn    = $l->getShortName();
      $ln    = $l->getLongName(false);
      echo "<td class='languageCell removeParent'>"
         . "<a class='tableLink color-language' $href title='$ln\n$ttip'>$sn</a>"
         . "<a $lhref title='$lttip' class='remove'><i class='icon-remove-custom'></i></a>"
         . "</td><td>"
         . "<i class='icon-eject rotate90 multitablePlayWe' title='$ptip'></i>"
         . "</td>";
    }else{
      echo "<td class='languageCell color-language' colspan='2'>"
         . $t->st('tabulator_multi_langrow').' '.($lc+1)
         . "</td><td></td>";
    }
    //Displaying the words:
    $wc    = 0;
    $words = $v->getWords();
    while(($w = array_shift($words)) || $wc < 3){
      $cell = '';
      if($w != null && $l != null){ // Output of a transcription
        $tr = new TranscriptionFromWordLang($w, $l);
        $alt = '';
        if($spelling = $tr->getAltSpelling($v))
          $alt = "<div class='altSpelling'>$spelling</div>";
        $pho   = $tr->getPhonetic($v, true);
        $cell  = $alt.$pho;
      }else if($wc < 3 && $lc < 3){
        if($wc == 1 || $lc == 1)
          if(count($v->getLanguages()) < 2 || count($v->getWords()) < 2)
            $cell = $t->st("tabulator_multi_cell$lc$wc");
      }
      echo "<td class='transcription'>$cell</td>";
      $wc++;
    }
    //Row finished.
    echo '</tr>';
    $lc++;
  } //Table complete
  echo '</tbody></table>';
}
?>
