<?php
require_once 'tables/Multitable.php';
require_once 'tables/MultitableTransposed.php';
/***/
class Tabulator{
  private $valueManager = null;
  private $pageView     = null;
  /***/
  public function __construct($valueManager){
    $this->valueManager = $valueManager;
    $this->pageView = $valueManager->gpv();
  }
  /***/
  public function wordHeadline($word){
    $v = $this->valueManager;
    $t = $v->getTranslator();
    //Getting started:
    $wordHeadline = array(
      'name' => $word->getLongName()
    );
    if(!$wordHeadline['name'])
      $wordHeadline['name'] = $word->getWordTranslation($v, true, false);
    if(!$v->gpv()->isView('MapView'))
      $wordHeadline['mapsLink'] = $word->getMapsLink($t);
    //Previous Word:
    if($p = $word->getPrev($v)){
      $wordHeadline['prev'] = array(
        'link'  => $v->setWord($p)->link()
      , 'ttip'  => $p->getLongName()
      , 'trans' => $p->getWordTranslation($v, true, false)
      , 'title' => $t->st('tabulator_word_prev')
      );
    }
    //Next Word:
    if($n = $word->getNext($v)){
      $wordHeadline['next'] = array(
        'link'  => $v->setWord($p)->link()
      , 'ttip'  => $n->getLongName()
      , 'trans' => $n->getWordTranslation($v, true, false)
      , 'title' => $t->st('tabulator_word_next')
      );
    }
    //Done:
    return $wordHeadline;
  }
  /***/
  public function tabulateWord($word){
    $v = $this->valueManager;
    $t = $v->getTranslator();
    //Calculating the maximum number of language cols:
    $maxLangCount = 0;
    foreach($v->getStudy()->getRegions() as $r){
      $c = $r->getLanguageCount();
      if($c > $maxLangCount)
        $maxLangCount = ($c > 6) ? 6 : $c;
    }
    //Building the table:
    $wordTable = array(
      'wordHeadlinePlayAll' => $t->st('wordHeadline_playAll')
    , 'wordHeadline'        => $this->wordHeadline($word)
    , 'rows'                => array()
    );
    $colorFamily = $v->getStudy()->getColorByFamily();
    foreach($v->getStudy()->getFamilies() as $fIx => $f){
      //The family cell:
      $family = array(
        'rowSpan' => 0
      , 'name'    => $f->getName()
      );
      if($colorFamily)
        $family['color'] = $f->getColor();
      //Regions:
      $regions     = array();
      $firstRegion = true;
      foreach($f->getRegions() as $r){
        if($firstRegion){
          $firstRegion = false;
        }else{
          $row['data-i'] = $fIx;
        }
        $languages = $r->getLanguages();
        $region    = array('name' => $r->getShortName());
        //Calculating the rSpan for a region:
        $rSpan = ceil(count($languages)/6);
        if($rSpan == 0) $rSpan++;
        $region['rowSpan']  = $rSpan;
        $family['rowSpan'] += $rSpan;
        //Color:
        if(!$colorFamily)
          $region['color'] = $r->getColor();
        //Languages:
        $cellCount = $maxLangCount;
        $lss       = array(); // [[LanguageCells]]
        $ls        = array();
        foreach($languages as $l){
          if($cellCount == 0){
            array_push($lss, $ls);
            $ls        = array();
            $cellCount = $maxLangCount;
          }
          $cell = array(
            'isLanguageCell' => true
          , 'link'           => $v->gpv()->setView('LanguageView')->setLanguage($l)->link()
          , 'shortName'      => $l->getShortName()
          , 'longName'       => $l->getLongName(false)
          );
          $tr = Transcription::getTranscriptionForWordLang($word, $l);
          if($s = $tr->getAltSpelling($v))
            $cell['spelling'] = $s;
          $cell['phonetic']   = $tr->getPhonetic($v, true);
          array_push($ls, $cell);
          $cellCount--;
        }
        for(;$cellCount > 0; $cellCount--)
          array_push($ls, array('isLanguageCell' => true));
        array_push($lss, $ls);
        //Filling $regions with generated rows:
        for($i = 0; $i < count($lss); $i++){
          $x = $lss[$i];
          if($i === 0)
            array_unshift($x, $region);
          array_push($regions, $x);
        }
      }
      //Adding to the rows:
      $row = ($fIx !== 0) ? array('spaceRow' => true, 'cells' => array()) : array('cells' => array());
      for($i = 0; $i < count($regions); $i++){
        $cs  = $regions[$i];
        if($i === 0)
          array_unshift($cs, $family);
        $row['cells'] = $cs;
        array_push($wordTable['rows'], $row);
        $row = array('cells' => array());
      }
    }
    echo Config::getMustache()->render('WordTable', $wordTable);
  }
  /**
    @param transposed [Bool = false] - will cause the table to display transposed.
  */
  function multiwordTable($transposed = false){
    $v = $this->valueManager;
    if(!$transposed){
      tables_multiwordTable($v);
    }else{
      tables_multiwordTableTransposed($v);
    }
  }
  /**
    @param $language Language
  */
  public function languageHeadline($language){
    $v = $this->valueManager;
    $t = $v->getTranslator();
    //Basic information:
    $headline = array(
      'longName'    => $language->getLongName()
    , 'links'       => $language->getLinks($t)
    , 'description' => $language->getDescription($t)
    , 'playAll'     => $t->st('language_playAll')
    );
    //Previous Language:
    $prev = array('title' => $t->st('tabulator_language_prev'));
    $lang = $language->getPrev($v);
    $prev['link']  = $v->setLanguage($lang)->link();
    $prev['trans'] = $lang->getShortName(false);
    $headline['prev'] = $prev;
    //Next Language:
    $next = array('title' => $t->st('tabulator_language_next'));
    $lang = $language->getNext($v);
    $next['link']  = $v->setLanguage($lang)->link();
    $next['trans'] = $lang->getShortName(false);
    $headline['next'] = $next;
    //Contributors:
    $contributors = array();
    $_v = $v->gpv()->setView('whoAreWe');
    foreach($language->getContributors() as $c){
      $cont = array(
        'cdesc' => $c->getColumnDescription()
      , 'link'  => $_v->link('', 'href', '#'.$c->getInitials())
      , 'name'  => $c->getName()
      );
      $cdesc = $c->getColumnDescription();
      $year  = $c->year;
      $pages = $c->pages;
      $name  = $c->getName();
      $link  = $_v->link('', 'href', '#'.$c->getInitials());
      if($year && $pages){
        $cont['info'] = "($year : $pages)";
      }else if($year || $pages){
        $cont['info'] = "($year$pages)";
      } else $cont['info'] = '';
      array_push($contributors, $cont);
    }
    $headline['contributorTooltip'] = $t->st('tooltip_contributor_list');
    $headline['hasContributors']    = count($contributors) > 0;
    $headline['contributors']       = $contributors;
    //Done:
    return $headline;
  }
  public function languageTable($language){
    $v = $this->valueManager;
    $t = $v->getTranslator();
    $table = array(
      'languageHeadline' => $this->languageHeadline($language)
    );
    //Constructing transcriptions:
    $transcriptions = array();
    foreach($v->getStudy()->getWords($v) as $w){
      $tr = Transcription::getTranscriptionForWordLang($w, $language);
      if(!$tr->exists()) continue;
      array_push($transcriptions, array(
        'link'     => $v->gpv()->setView('WordView')->setLanguages(array())->setWord($w)->link()
      , 'ttip'     => $w->getLongName()
      , 'trans'    => $w->getWordTranslation($v, true, false)
      , 'spelling' => $tr->getAltSpelling($v)
      , 'phonetic' => $tr->getPhonetic($v, true)
      ));
    }
    //Filling the rows by 6:
    $rows = array();
    foreach(array_chunk($transcriptions, 6) as $ts){
      array_push($rows, array('transcriptions' => $ts));
    }
    $table['rows'] = $rows;
    echo Config::getMustache()->render('LanguageTable', $table);
  }
  /**
    @return json String - JSON encoded information to show on the map.
  */
  public function mapsData(){
    $v = $this->valueManager;
    $transcriptions = array();
    foreach($v->getLanguages() as $l){
      if(!$l->getLocation())
        continue;
      foreach($v->getWords() as $w){
        $t = Transcription::getTranscriptionForWordLang($w, $l);
        array_push($transcriptions, $t->toJSON());
      }
    }
    $data = array(
      'regionZoom'     => $v->getStudy()->getMapZoomCorners()
    , 'transcriptions' => $transcriptions
    );
    return json_encode($data);
  }
}
?>
