<?php
/*
  Echoes a multiwordtable for a given valueManager.
*/
function tables_multiwordTable($v){
  //Setup:
  $t          = $v->getTranslator();
  $words      = $v->getWords();
  $languages  = Language::mkRegionBuckets($v->getLanguages());
  $regions    = $languages['regions']; // RegionId -> Region
  $languages  = $languages['buckets']; // RegionId -> Language[]
  $clearRow   = "<tr><td colspan='".(count($words) + 3)."' class='spaceRow'></td></tr>";
  $lastFamily = -1;
  if(count($words) === 0) $clearRow = "<tr><td colspan='6' class='spaceRow'></td></tr>";
  //thead is composed out of three rows: the MeaningGroups, The delete all + Words, and the plays:
  echo '<table id="multitable" class="table table-bordered table-striped"><thead>';
  //The MeaningGroups:
  if($v->gwo()->isLogical() && count($words) !== 0){
    $mgs = Word::mkMGBuckets($words);
    $mgc = $mgs['buckets']; // mId -> Word[]
    $mgs = $mgs['mgs'];     // mId -> MeaningGroup
    echo '<tr><th></th><th></th><th></th>';
    foreach($mgs as $mId => $mg){
      $name = $mg->getName();
      $span = count($mgc[$mId]);
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
  if(count($words) === 0){
    for($i = 1; $i <= 3; $i++)
      echo '<th class="color-word">'.$t->st('tabulator_multi_wordcol').' '.$i.'</th>';
  }else foreach($words as $w){
    $dhref = $v->delWord($w)->setUserCleaned()->link();
    $dttip = $t->st('tabulator_multi_tooltip_removeWord');
    $href  = $v->gpv()->setView('WordView')->setLanguages()->setWord($w)->link();
    $ttip  = ($ln = $w->getLongName()) ? " title='$ln'" : '';
    $ptip  = $t->st('tabulator_multi_playword');
    $trans = $w->getTranslation($v, true, false);
    $maps  = $w->getMapsLink($t);
    echo "<th>"
       . "<a $dhref title='$dttip' class='remove'><i class='icon-remove-custom'></i></a>"
       . "<a class='tableLink color-word' $href$ttip>$trans</a>"
       . "</th>";
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
  if(count($words) === 0){
    echo '<th class="icons"></th><th class="icons"></th><th class="icons"></th>';
  }else foreach($words as $w){
    $ptip = $t->st('tabulator_multi_playword');
    $maps = $w->getMapsLink($t);
    echo "<th class='icons'>$maps"
       . "<i class='icon-eject rotate180 multitablePlayNs' title='$ptip'></i>"
       . "</th>";
  }
  //thead finished, starting body.
  echo '</tr></thead><tbody>';
  //Case that regions are empty:
  if(count($regions) === 0){
    for($i = 1; $i <= 3; $i++){
      echo '<tr><td class="languageCell color-language" colspan="3">'.$t->st('tabulator_multi_langrow').' '.$i.'</td>';
      for($j = 0; $j < 3; $j++){
        $c = ($i !== 2 && $j !== 1) ? ''
           : $t->st('tabulator_multi_cell'.($i-1).$j);
        echo '<td class="transcription">'.$c.'</td>';
      }
      if(count($words) > 3)
        echo '<td class="transcription" colspan="'.(count($words) - 3).'"></td>';
      echo '</tr>';
    }
    echo '</tr>';
  //Usual case where we iterate all regions:
  }else foreach($regions as $rId => $r){
    //Class of the regionrow:
    $rowClass = "regionNormal";
    //Handling of clearRows depending on the regionType:
    $fId = $r->getFamily()->getId();
    if($fId != $lastFamily){
      $lastFamily = $fId;
      echo $clearRow;
    }
    //The Regionrow begins:
    echo '<tr>';
    //Typical output for the region:
    $rspan  = count($languages[$rId]);
    $name   = $r->getShortName();
    $rColor = $r->getColorStyle();
    echo "<td class='regionCell color-region' rowspan='$rspan'$rColor>$name</td>";
    //The languages:
    $first = true;
    foreach($languages[$rId] as $i => $l){
      //Is this language in a new row?
      if(!$first){
        echo '<tr>';
      }else $first = false;
      //Displaying the language:
      $lhref = $v->delLanguage($l)->setUserCleaned()->link();
      $lttip = $t->st('tabulator_multi_tooltip_removeLanguage');
      $ttip  = $l->getLongName(false);
      $ptip  = $t->st('tabulator_multi_playlang');
      $href  = $v->gpv()->setView('LanguageView')->setWords()->setLanguage($l)->link();
      $sn    = $l->getShortName();
      echo "<td class='languageCell'>"
         . "<a class='tableLink color-language' $href title='$ttip'>$sn</a>"
         . "<a $lhref title='$lttip' class='remove'><i class='icon-remove-custom'></i></a>"
         . "</td><td>"
         . "<i class='icon-eject rotate90 multitablePlayWe' title='$ptip'></i>"
         . "</td>";
      //Displaying the words:
      if(count($words) === 0){
        for($j = 0; $j < 3; $j++){
          $c = '';
          if(($i === 1 || $j === 1) && $i < 3)
            $c = $t->st('tabulator_multi_cell'.$i.$j);
          echo '<td class="transcription">'.$c.'</td>';
        }
      }else foreach($words as $w){
        $tr = new TranscriptionFromWordLang($w, $l);
        if($spelling = $tr->getAltSpelling($v)){
          $alt = "<div class='altSpelling'>$spelling</div>";
        }else $alt = '';
        $pho = $tr->getPhonetic($v, true);
        echo "<td class='transcription'>$alt$pho</td>";
      }
      //The row ends:
      echo '</tr>';
    }
  } //Table complete
  echo '</tbody></table>';
}
?>
