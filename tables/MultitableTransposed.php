<?php
/*
  Echoes a multiwordtable for a given valueManager.
*/
function tables_multiwordTableTransposed($v){
  //Setup:
  $t         = $v->getTranslator();
  $languages = Language::mkRegionBuckets($v->getLanguages());
  $regions   = $languages['regions']; // RegionId -> Region
  $languages = $languages['buckets']; // RegionId -> Language[]
  $words     = Word::mkMGBuckets($v->getWords());
  $mgs       = $words['mgs'];
  $words     = $words['buckets'];
  $table     = array(
    'isLogical' => $v->gwo()->isLogical()
  , 'deleteAll' => $t->st('tabulator_multi_clear_all')
  , 'clearLanguagesLink' => $v->setLanguages()->setUserCleaned()->link()
  , 'clearLanguagesText' => $t->st('tabulator_multi_clear_languages')
  , 'clearWordsLink' => $v->setWords()->setUserCleaned()->link()
  , 'clearWordsText' => $t->st('tabulator_multi_clear_words')
  , 'transposeLink'  => $v->gpv()->transpose()->link()
  , 'transposeTtip'  => $t->st('tabulator_multi_transpose')
  , 'regions'   => array()
  , 'languages' => array()
  , 'rows'      => array()
  );
  //The thead, consisting of rows for: regions, delete and languages, plays:
  //The Regions:
  foreach($regions as $rId => $r){
    array_push($table['regions'], array(
      'cspan' => count($languages[$rId])
    , 'color' => $r->getColorStyle()
    , 'name'  => $r->getShortName()
    ));
  }
  //The Languages:
  $languages = __($languages)->flatten();
  if(count($languages) === 0){
    for($i = 1; $i <= 3; $i++){
      array_push($table['languages'], array(
        'isFake'    => true
      , 'shortName' => $t->st('tabulator_multi_langrow').' '.$i
      ));
    }
  }else foreach($languages as $l){
    array_push($table['languages'], array(
      'isFake'    => false
    , 'shortName' => $l->getShortName()
    , 'link'      => $v->gpv()->setView('LanguageView')->setWords()->setLanguage($l)->link()
    , 'playTtip'  => $t->st('tabulator_multi_playlang')
    , 'ttip'      => $l->getLongName(false)
    , 'deleteLanguageTtip' => $t->st('tabulator_multi_tooltip_removeLanguage')
    , 'deleteLanguageLink' => $v->delLanguage($l)->setUserCleaned()->link()
    ));
  }
  //thead complete.
  //Content for the rows:
  //Generating MeaningGroups:
  $meaningGroups = array();
  if($table['isLogical']){
    $clearRow = 2 + count($languages);
    if(count($languages) === 0) $clearRow += 3;
    if($table['isLogical']) $clearRow++;
    foreach($mgs as $mId => $m){
      array_push($meaningGroups, array(
        'clearRow' => $clearRow
      , 'name'     => $m->getName()
      , 'rSpan'    => count($words[$mId])
      ));
    }
  }
  //Gathering words:
  $wds = array();
  if(count($mgs) > 0){
    if($table['isLogical']){
      foreach($mgs as $mId => $m){
        array_push($wds, $words[$mId]);
      }
      $wds = __($wds)->flatten();
    }else{
      $wds = $v->getWords();
    }
  }
  //Building arrays in $wds:
  if(count($wds) === 0){
    $pre = $t->st('tabulator_multi_wordcol');
    for($j = 0; $j < 3; $j++){
      array_push($wds, array(
        'fake'  => true
      , 'trans' => $pre.' '.($j+1)
      ));
    }
  }else{
    $wds = __($wds)->map(function($w) use($v, $t){
      return array(
        'link'           => $v->gpv()->setView('WordView')->setLanguages()->setWord($w)->link()
      , 'ttip'           => $w->getLongName()
      , 'trans'          => $w->getWordTranslation($v, true, false)
      , 'deleteWordLink' => $v->delWord($w)->setUserCleaned()->link()
      , 'deleteWordTtip' => $t->st('tabulator_multi_tooltip_removeWord')
      , 'maps'           => $w->getMapsLink($t)
      , 'playTtip'       => $t->st('tabulator_multi_playword')
      , 'fake'           => $w // Convert to false later.
      );
    });
  }
  //Building the transcriptions:
  $lCount = $iMax = count($languages);
  if($iMax === 0) $iMax = 3;
  $wds = __($wds)->map(function($w, $j) use ($v, $t, $lCount, $iMax, $languages, $table){
    $w['transcriptions'] = array();
    $w['isLogical'] = $table['isLogical'];
    //Case either Word or Language are missing:
    if($w['fake'] === true || $lCount === 0){
      for($i = 0; $i < $iMax; $i++){
        $tr = array('fake' => '');
        if($i < 3 && $j < 3){
          if($i === 1 || $j === 1){
            $tr['fake'] = $t->st("tabulator_multi_cell$i$j");
          }
        }
        array_push($w['transcriptions'], $tr);
      }
    }else{
      $w['transcriptions'] = __($languages)->map(function($l) use ($v, $w){
        $tr = Transcription::getTranscriptionForWordLang($w['fake'], $l);
        return array(
          'spelling' => $tr->getAltSpelling($v)
        , 'phonetic' => $tr->getPhonetic($v, true)
        );
      });
    }
    //Rewriting fake to false:
    if($w['fake'] !== true) $w['fake'] = false;
    return $w;
  });
  //Composing the rows:
  foreach($meaningGroups as $mg){
    $x  = __($wds);
    $r  = $mg['rSpan'];
    $ws = $x->first($r);
    foreach($ws as $w){
      $row = array('words' => array($w));
      if($mg !== null){
        $row['meaningGroup'] = $mg;
        $mg = null;
      }
      array_push($table['rows'], $row);
    }
    $wds = $x->rest($r);
  }
  if(count($wds) > 0){
    foreach($wds as $w){
      array_push($table['rows'], array('words' => $w));
    }
  }
  //Complete table:
  return $table;
}
?>
