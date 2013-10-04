<?php
/*
  Echoes a multiwordtable for a given valueManager.
*/
function tables_multiwordTableTransposed($v){
  //Setup:
  $t          = $v->getTranslator();
  $languages  = Language::mkRegionBuckets($v->getLanguages());
  $regions    = $languages['regions']; // RegionId -> Region
  $languages  = $languages['buckets']; // RegionId -> Language[]
  $words      = Word::mkMGBuckets($v->getWords());
  $mgs        = $words['mgs'];
  $words      = $words['buckets'];
  $lOffset    = $v->gwo()->isLogical() ? '<th></th>' : '';
  //The thead, consisting of rows for: regions, delete and languages, plays:
  echo '<table id="multitabletrans" class="table table-bordered table-striped"><thead>';
  //The Regions:
  echo "<tr><th></th><th></th>$lOffset";
  foreach($regions as $rId => $r){
    $cspan = count($languages[$rId]);
    $name  = $r->getShortName();
    $color = $r->getColorStyle();
    echo "<th colspan='$cspan'$color>$name</th>";
  }
  echo '</tr>';
  //Delete All:
  $deleteAll = $t->st('tabulator_multi_clear_all');
  $lhref     = $v->setLanguages()->setUserCleaned()->link();
  $lclean    = $t->st('tabulator_multi_clear_languages');
  echo "<tr>$lOffset<th>$deleteAll "
     . "<a $lhref class='color-language'>$lclean<i class='icon-arrow-right'></i></a></th><th></th>";
  //The Languages:
  $languages = __($languages)->flatten();
  if(count($languages) === 0){
    for($i = 1; $i <= 3; $i++)
      echo '<th class="languageCell color-language">'
         . $t->st('tabulator_multi_langrow').' '.($i+1)
         . '</th>';
  }else foreach($languages as $l){
    $lhref = $v->delLanguage($l)->setUserCleaned()->link();
    $lttip = $t->st('tabulator_multi_tooltip_removeLanguage');
    $ttip  = $t->st('tabulator_multi_langonly');
    $ptip  = $t->st('tabulator_multi_playlang');
    $href  = $v->gpv()->setView('LanguageView')->setWords()->setLanguage($l)->link();
    $sn    = $l->getShortName();
    $ln    = $l->getLongName(false);
    echo "<th class='languageCell'>"
       . "<a $lhref title='$lttip' class='remove'><i class='icon-remove-custom'></i></a>"
       . "<a class='tableLink color-language' $href title='$ln\n$ttip'>$sn</a><br />"
       . "</th>";
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
  $lc = count($languages);
  if($lc === 0) $lc = 3;
  $ptip = $t->st('tabulator_multi_playlang');
  for($i = 0; $i < $lc; $i++)
    echo "<th><i class='multitablePlayNs icon-eject rotate180' title='$ptip'></i></th>";
  $lc = $lc >= 3 ? 0 : 3 - $lc;
  for($i = 0; $i < $lc; $i++)
    echo '<th></th>';
  echo '</tr>';
  //thead complete.
  echo '</thead><tbody>';
  //Building the clearRow:
  $clearRow = 2 + count($languages);
  if(count($languages) === 0) $clearRow += 3;
  if($v->gwo()->isLogical()) $clearRow++;
  $clearRow = "<td colspan='$clearRow' class='spaceRow'></td></tr>";
  echo "<tr>$clearRow";
  $clearRow .= '<tr>';
  //Displaying a single word:
  $showWord = function ($j, $w) use ($v, $t){
    $mhref = $v->delWord($w)->setUserCleaned()->link();
    $mttip = $t->st('tabulator_multi_tooltip_removeWord');
    $href  = $v->gpv()->setView('WordView')->setLanguages()->setWord($w)->link();
    $ttip  = $t->st('tabulator_multi_wordonly');
    $ptip  = $t->st('tabulator_multi_playword');
    $trans = $w->getTranslation($v, true, false);
    $maps  = $w->getMapsLink($t);
    echo "<td><a class='tableLink color-word' $href title='$ttip'>$trans</a>"
       . "<a $mhref title='$mttip' class='remove'><i class='icon-remove-custom'></i></a>"
       . "</td><td class='icons'>$maps"
       . "<i class='icon-eject rotate90 multitablePlayWe' title='$ptip'></i>"
       . "</td>";
  };
  /**
    @param $i ix of the language
    @param $j ix of the word
    @param $l the language
    @param $w the word
    Displaying a single Language.
  */
  $showLanguage = function ($i, $j, $l = null, $w = null) use ($v, $t){
    $cell = '';
    if($w != null && $l != null){ // Output of a transcription
      $tr = new TranscriptionFromWordLang($w, $l);
      $alt = '';
      if($spelling = $tr->getAltSpelling($v))
        $alt = "<div class='altSpelling'>$spelling</div>";
      $pho   = $tr->getPhonetic($v, true);
      $cell  = $alt.$pho;
    }else if($j < 3 && $i < 3){
      if($i == 1 || $j == 1)
        if(count($v->getLanguages()) < 2 || count($v->getWords()) < 2)
          $cell = $t->st("tabulator_multi_cell$i$j");
    }
    echo "<td class='transcription'>$cell</td>";
  };
  //Iterating the words:
  if(count($mgs) === 0){ //No MeaningGroups or Words
    for($j = 0; $j < 3; $j++){
      echo '<tr>'.$lOffset.'<td class="color-word">'
         . $t->st('tabulator_multi_wordcol').' '.($j+1)
         . '</td><td></td>';
      $iMax = count($languages);
      if($iMax === 0) $iMax = 3;
      for($i = 0; $i < $iMax; $i++)
        $showLanguage($i,$j);
      echo '</tr>';
    }
  }else if($v->gwo()->isLogical()){ //We iterate over the MeaningGroups
    foreach($mgs as $mId => $m){
      $rSpan = count($words[$mId]);
      $name  = $m->getName();
      echo '<tr>'.$clearRow
         . '<td class="regionCell color-region" rowspan="'.$rSpan.'">'
         . $name.'</td>';
      foreach($words[$mId] as $j => $w){
        $showWord($j, $w);
        if(count($languages) === 0){
          for($i = 0; $i < 3; $i++)
            $showLanguage($i,$j,null,$w);
        }else foreach($languages as $i => $l)
          $showLanguage($i,$j,$l,$w);
        echo '</tr>';
      }
    }
  }else{ //We iterate over the Words
    $words = $v->getWords();
    foreach($words as $j => $w){
      echo '<tr>';
      $showWord($j, $w);
      if(count($languages) === 0){
        for($i = 0; $i < 3; $i++)
          $showLanguage($i,$j,null,$w);
      }else foreach($languages as $i => $l)
        $showLanguage($i,$j,$l,$w);
      echo '</tr>';
    }
  }
  //Table complete
  echo '</tbody></table>';
}
?>
