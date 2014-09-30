<?php
/*
  Echoes a multiwordtable for a given valueManager.
*/
function tables_multiwordTable($v){
  //Setup:
  $t         = $v->getTranslator();
  $words     = $v->getWords();
  $languages = Language::mkRegionBuckets($v->getLanguages());
  $regions   = $languages['regions']; // RegionId -> Region
  $languages = $languages['buckets']; // RegionId -> Language[]
  $table     = array(
    'displayMGs'         => ($v->gwo()->isLogical() && count($words) !== 0)
  , 'deleteAll'          => $t->st('tabulator_multi_clear_all')
  , 'clearWordsLink'     => $v->setWords()->setUserCleaned()->link()
  , 'clearWordsText'     => $t->st('tabulator_multi_clear_words')
  , 'clearLanguagesLink' => $v->setLanguages()->setUserCleaned()->link()
  , 'clearLanguagesText' => $t->st('tabulator_multi_clear_languages')
  , 'transposeTtip'      => $t->st('tabulator_multi_transpose')
  , 'transposeLink'      => $v->gpv()->transpose()->link()
  );
  //thead is composed out of three rows: the MeaningGroups, The delete all + Words, and the plays:
  //The MeaningGroups:
  if($table['displayMGs']){
    $mgs  = Word::mkMGBuckets($words);
    $mgc  = $mgs['buckets']; // mId -> Word[]
    $mgs  = $mgs['mgs'];     // mId -> MeaningGroup
    $grps = array();
    foreach($mgs as $mId => $mg){
      array_push($grps, array(
        'name' => $mg->getName()
      , 'span' => count($mgc[$mId])
      ));
    }
    $table['meaningGroups'] = $grps;
  }
  //The Words:
  $wds = array();
  if(count($words) === 0){
    for($i = 1; $i <= 3; $i++)
      array_push($wds, array(
        'isFake' => true
      , 'trans'  => $t->st('tabulator_multi_wordcol').' '.$i
      ));
  }else foreach($words as $w){
    array_push($wds, array(
      'deleteLink' => $v->delWord($w)->setUserCleaned()->link()
    , 'deleteTtip' => $t->st('tabulator_multi_tooltip_removeWord')
    , 'link'       => $v->gpv()->setView('WordView')->setLanguages()->setWord($w)->link()
    , 'ttip'       => ($ln = $w->getLongName()) ? " title='$ln'" : ''
    , 'trans'      => $w->getWordTranslation($v, true, false)
    , 'playTtip'   => $t->st('tabulator_multi_playword')
    , 'map'        => $w->getMapsLink($t)
    , 'isFake'     => false
    ));
  }
  $table['words'] = $wds;
  //Case that regions are empty:
  $lastFamily = -1;
  $clearRow   = array(
    'colspan' => (count($words) === 0) ? 6 : (count($words) + 3)
  );
  $rgs = array();
  if(count($regions) === 0){
    $lgs = array();
    for($i = 1; $i <= 3; $i++){
      $ts = array();
      for($j = 0; $j < 3; $j++){
        array_push($ts, array(
          'isFake' => true
        , 'fake'   => ($i !== 2 && $j !== 1) ? '' : $t->st('tabulator_multi_cell'.($i-1).$j)
        ));
      }
      if(count($words) > 3){
        array_push($ts, array(
          'isFake'  => true
        , 'colspan' => count($words) - 3
        ));
      }
      array_push($lgs, array(
        'isFake'         => true
      , 'shortName'      => $t->st('tabulator_multi_langrow').' '.$i
      , 'transcriptions' => $ts
      , 'isFirst'        => ($i === 1)
      ));
    }
    array_push($rgs, array(
      'isFake'    => true
    , 'languages' => $lgs
    ));
  //Usual case where we iterate all regions:
  }else foreach($regions as $rId => $r){
    $rgn = array(
      'isFake' => false
    , 'rspan'  => count($languages[$rId])
    , 'rColor' => $r->getColorStyle()
    , 'name'   => $r->getShortName()
    );
    //Handling of clearRows depending on the regionType:
    $fId = $r->getFamily()->getId();
    if($fId != $lastFamily){
      $lastFamily    = $fId;
      $rgn['clearRow'] = $clearRow;
    }
    //The languages:
    $lgs = array();
    foreach($languages[$rId] as $i => $l){
      $lng = array(
        'link'         => $v->gpv()->setView('LanguageView')->setWords()->setLanguage($l)->link()
      , 'languageTtip' => $l->getLongName(false)
      , 'shortName'    => $l->getShortName()
      , 'deleteLink'   => $v->delLanguage($l)->setUserCleaned()->link()
      , 'deleteTtip'   => $t->st('tabulator_multi_tooltip_removeLanguage')
      , 'playTtip'     => $t->st('tabulator_multi_playlang')
      );
      //The transcriptions:
      $ts = array();
      if(count($words) === 0){
        for($j = 0; $j < 3; $j++){
          $c = '';
          if(($i === 1 || $j === 1) && $i < 3)
            $c = $t->st('tabulator_multi_cell'.$i.$j);
          array_push($ts, array(
            'isFake' => true
          , 'fake'   => $c
          ));
        }
      }else foreach($words as $w){
        $tr  = Transcription::getTranscriptionForWordLang($w, $l);
        $tsc = array(
          'isFake'   => false
        , 'phonetic' => $tr->getPhonetic($v, true)
        );
        if($spelling = $tr->getAltSpelling($v)){
          $tsc['spelling'] = $spelling;
        }
        array_push($ts, $tsc);
      }
      $lng['transcriptions'] = $ts;
      array_push($lgs, $lng);
    }
    $lgs[0]['first']  = true;
    $rgn['languages'] = $lgs;
    array_push($rgs, $rgn);
  }
  //Table complete:
  $table['regions'] = $rgs;
  return $table;
}
?>
