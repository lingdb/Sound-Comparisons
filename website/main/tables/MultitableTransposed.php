<?php
/*
  Echoes a multiwordtable for a given valueManager.
*/
function tables_multiwordTableTransposed($v){
  $t = $v->getTranslator();
  echo '<table id="multitabletrans" class="table table-bordered table-striped">';
  $lOffset = $v->gwo()->isLogical() ? '<th></th>' : '';
  //Precalculating the regions:
  $spansum   = 0; //Sum of all colspans
  $rc        = array(); // Region->getId() => colspan
  $regions   = array(); // Region->getId() => Region
  $languages = $v->getLanguages();
  foreach($languages as $l){
    $r   = $l->getRegion();
    $rId = $r->getId();
    if(!array_key_exists($rId, $rc)){
      $rc[$rId] = 0;
    }
    $rc[$rId]      = $rc[$rId] + 1;
    $regions[$rId] = $r;
  }
  //The thead, consisting of rows for: regions, delete and languages, plays:
  echo '<thead>';
  //The Regions:
  echo "<tr><th></th><th></th>$lOffset";
  foreach($regions as $rId => $r){
    $cspan = $rc[$rId];
    $name  = $r->getShortName();
    $color = $r->getColorStyle();
    echo "<th colspan='$cspan'$color>$name</th>";
    $spansum += $cspan;
  }
  echo '</tr>';
  //Delete All:
  $deleteAll = $t->st('tabulator_multi_clear_all');
  $lhref     = $v->setLanguages()->setUserCleaned()->link();
  $lclean    = $t->st('tabulator_multi_clear_languages');
  echo "<tr>$lOffset<th>$deleteAll "
     . "<a $lhref class='color-language'>$lclean<i class='icon-arrow-right'></i></a></th><th></th>";
  //The Languages:
  $lc = 0;
  while(($l = array_shift($languages)) || $lc < 3){
    if($l){
      $lhref = $v->delLanguage($l)->setUserCleaned()->link();
      $lttip = $t->st('tabulator_multi_tooltip_removeLanguage');
      $ttip  = $t->st('tabulator_multi_langonly');
      $ptip  = $t->st('tabulator_multi_playlang');
      $href  = $v->gpv()->setView('LanguageView')->setWords()->setLanguage($l)->link();
      $sn    = $l->getShortName();
      $ln    = $l->getLongName(false);
      echo "<th class='languageCell removeParent'>"
         . "<a $lhref title='$lttip' class='remove'><i class='icon-remove-custom'></i></a>"
         . "<a class='tableLink color-language' $href title='$ln\n$ttip'>$sn</a><br />"
         . "</th>";
    }else{
      $dummyL = $t->st('tabulator_multi_langrow').' '.($lc+1);
      echo "<th class='languageCell color-language'>$dummyL</th>";
    }
    $lc++;
  }
  echo '</tr>';
  //Delete all Words:
  $whref  = $v->setWords()->setUserCleaned()->link();
  $wclean = $t->st('tabulator_multi_clear_words');
  echo "$lOffset</th><th>$deleteAll "
     . "<a $whref class='color-word'>$wclean<i class='icon-arrow-down'></i></a></th>";
  //Transpose:
  $trTtip = $t->st('tabulator_multi_transpose');
  $trHref = $v->gpv()->transpose()->link();
  echo "<th><a $trHref><img src='img/transpose.png' title='$trTtip' /></a></th>";
  //The plays:
  $lc = count($v->getLanguages());
  $ptip = $t->st('tabulator_multi_playlang');
  for($i = 0; $i < $lc; $i++)
    echo "<th><i class='multitablePlayNs icon-eject rotate180' title='$ptip'></i></th>";
  $lc = $lc >= 3 ? 0 : 3 - $lc;
  for($i = 0; $i < $lc; $i++)
    echo '<th></th>';
  echo '</tr>';
  //thead complete.
  echo '</thead><tbody>';
  //To build the table body, we need fields for potential MeaningGroups:
  $mgr = array(); // MeaningGroup->getId() => rowspan
  $mgs = array(); // MeaningGroup->getId() => MeaningGroup
  if($v->gwo()->isLogical()){ //But we're only interested in MeaningGroups some times.
    foreach($v->getWords() as $w){
      foreach($w->getMeaningGroups() as $m){
        $mId = $m->getId();
        if(!array_key_exists($mId, $mgr))
          $mgr[$mId] = 0;
        $mgr[$mId] = $mgr[$mId] + 1;
        $mgs[$mId] = $m;
      }
    }
  }
  //Building the table rows:
  $spansum += 2 + (count($languages) < 3 ? 3 - count($languages) : 0);
  $clearRow = "<td colspan='$spansum' class='spaceRow'></td></tr>";
  echo "<tr>$clearRow";
  $clearRow .= '<tr>';
  $wc    = 0;
  $words = $v->getWords();
  while(($w = array_shift($words)) || $wc <3){//Oh look, a heart♥
    echo "<tr>";
    //A row might start with a meaningGroup:
    $wmgs = $w ? $w->getMeaningGroups() : array();
    if(count($wmgs) > 0){
      $mgKey = $wmgs[0]->getId();
      if(array_key_exists($mgKey, $mgs))
        if($m = $mgs[$mgKey]){
          if(isset($lastMg) && $lastMg != $m->getId())
            echo $clearRow;
          $lastMg = $m->getId();
          $mgs[$m->getId()] = null;
          $rspan = $mgr[$m->getId()];
          $name  = $m->getName();
          echo "<td class='regionCell color-region' rowspan='$rspan'>$name</td>";
        }
    }
    //A row has a word field:
    if($w){
      $mhref = $v->delWord($w)->setUserCleaned()->link();
      $mttip = $t->st('tabulator_multi_tooltip_removeWord');
      $href  = $v->gpv()->setView('WordView')->setLanguages()->setWord($w)->link();
      $ttip  = $t->st('tabulator_multi_wordonly');
      $ptip  = $t->st('tabulator_multi_playword');
      $trans = $w->getTranslation($v, true, false);
      $maps  = $w->getMapsLink($t);
      echo "<td class='removeParent'><a class='tableLink color-word' $href title='$ttip'>$trans</a>"
         . "<a $mhref title='$mttip' class='remove'><i class='icon-remove-custom'></i></a>"
         . "</td><td class='icons'>$maps"
         . "<i class='icon-eject rotate90 multitablePlayWe' title='$ptip'></i>"
         . "</td>";
    }else{
      $dummyW = $t->st('tabulator_multi_wordcol').' '.($wc+1);
      echo "<td class='color-word'>$dummyW</td><td></td>";
    }
    $lc = 0;
    $languages = $v->getLanguages();
    while(($l = array_shift($languages)) || $lc < 3){
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
      $lc++;
    }
    echo "</tr>";
    $wc++;
  }
  //Table complete
  echo '</tbody></table>';
}
?>
