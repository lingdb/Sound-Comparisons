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
    //Previous Word:
    $prev = '';
    if($p = $word->getPrev($v)){
      $href  = $v->setWord($p)->link();
      $trans = $p->getTranslation($v, true, false);
      $ttip  = '';
      if($ln = $p->getLongName()) $ttip = "title='$ln'";
      $prev  = $t->st('tabulator_word_prev');
      $prev  = "<a id='prevLink' $href $ttip class='pull-left nounderline'>"
             . "<div class='color-word inline'>$trans </div>← $prev</a>";
    }
    //Next Word:
    $next = '';
    if($n = $word->getNext($v)){
      $href  = $v->setWord($n)->link();
      $trans = $n->getTranslation($v, true, false);
      $ttip  = '';
      if($ln = $n->getLongName()) $ttip = "title='$ln'";
      $next  = $t->st('tabulator_word_next');
      $next  = "<a id='nextLink' $href $ttip class='pull-right nounderline'>"
             . "$next →<div class='color-word inline'> $trans</div></a>";
    }
    //Content:
    $mapsLink = $v->gpv()->isView('MapView') ? '' : $word->getMapsLink($t);
    $name = $word->getLongName();
    if(!$name) $name = $word->getTranslation($v, true, false);
    $content = '<h1 class="color-word">'
             . $name . $mapsLink
             . '</h1>';
    //Composition
    return "<div class='row-fluid'>"
         . "<div class='span3'>$prev</div>"
         . "<div class='span6 centertext' id='tableHeader'>$content</div>"
         . "<div class='span3'>$next</div>"
         . "</div>";
  }
  /***/
  public function tabluateWord($word){
    $v    = $this->valueManager;
    $t    = $v->getTranslator();
    //Calculating the maximum number of language cols:
    $maxLangCount = 0;
    foreach($v->getStudy()->getRegions() as $r){
      $c = $r->getLanguageCount();
      if($c > $maxLangCount)
        $maxLangCount = ($c > 6) ? 6 : $c;
    }
    //Building the table:
    $wordHeadlinePlayAll = $t->st('wordHeadline_playAll');
    $table = $this->wordHeadline($word)
           . '<table id="singleWordTable" class="table table-bordered table-striped"><tbody>'
           . "<i class='icon-eject rotate90' id='wordHeadline_playAll' title='$wordHeadlinePlayAll'></i>";
    $colorFamily = $v->getStudy()->getColorByFamily();
    foreach($v->getStudy()->getFamilies() as $fIx => $f){
      $row = ($fIx !== 0) ? '<tr><td class="spaceRow" colspan="6"></td></tr><tr>' : '<tr>';
      $rContent    = '';
      $rSpanSum    = 0;
      $firstRegion = true;
      foreach($f->getRegions() as $r){
        if($firstRegion){
          $firstRegion = false;
        }else
          $rContent .= "<tr data-i='$fIx'>";
        $languages   = $r->getLanguages();
        $rSpan       = ceil(count($languages)/6);
        if($rSpan   == 0) $rSpan++;
        $rSpanSum   += $rSpan;
        $color       = $colorFamily ? '' : ' style="background-color: #'.$r->getColor().';"';
        $rContent   .= "<th rowspan='$rSpan'$color>".$r->getShortName().'</th>';
        $cellCount   = $maxLangCount;
        foreach($languages as $l){
          if($cellCount == 0){
            $cellCount = $maxLangCount;
            $rContent .= "</tr><tr>";
          }
          $href = $v->gpv()->setView('LanguageView')->setLanguage($l)->link();
          $sn   = $l->getShortName();
          $ln   = $l->getLongName(false);
          $link = "<a class='tableLink color-language' $href title='$ln'>$sn</a><br />";
          $tr   = new TranscriptionFromWordLang($word, $l);
          $spelling = '';
          if($s = $tr->getAltSpelling($v))
            $spelling = "<div class='altSpelling' >$s</div>";
          $phonetic   = $tr->getPhonetic($v, true);
          $rContent  .= "<td>$link$spelling$phonetic</td>";
          $cellCount--;
        }
        for(;$cellCount > 0; $cellCount--)
          $rContent .= "<td></td>";
        $rContent   .= "</tr>";
      }
      $fName = $f->getName();
      $color = $colorFamily ? ' style="background-color: #'.$f->getColor().';"' : '';
      if($rSpanSum > 0)
        $table .= "$row<th rowSpan='$rSpanSum'$color>$fName</th>$rContent";
    }
    $table .= '</tbody></table>';
    echo $table;
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
    $longName = $language->getLongName();
    $links = $language->getLinks($t);
    $desc  = $language->getDescription($t);
    //Previous Language:
    $prev  = $t->st('tabulator_language_prev');
    $lang  = $language->getPrev($v);
    $href  = $v->setLanguage($lang)->link();
    $trans = $lang->getShortName(false);
    $prev  = "<a id='prevLink' $href class='pull-left nounderline'>"
           . "<div class='color-language inline'>$trans </div>← $prev</a>";
    //Next Language:
    $next  = $t->st('tabulator_language_next');
    $lang  = $language->getNext($v);
    $href  = $v->setLanguage($lang)->link();
    $trans = $lang->getShortName(false);
    $next  = "<a id='nextLink' $href class='pull-right nounderline'>$next →"
           . "<div class='color-language inline'> $trans</div></a>";
    //Contributors:
    $contributors = '';
    $_v = $v->gpv()->setView('whoAreWe');
    foreach($language->getContributors() as $c){
      $cdesc = $c->getColumnDescription();
      $year  = $c->year;
      $pages = $c->pages;
      $name  = $c->getName();
      $link  = $_v->link('', 'href', '#'.$c->getInitials());
      if($year && $pages){
        $info = "($year : $pages)";
      }else if($year || $pages){
        $info = "($year$pages)";
      } else $info = '';
      $contributors .= "<tr><th class='text-left'>$cdesc:</th><td>"
                     . "<a $link class='pull-right'>$name$info</a>"
                     . "</td></tr>";
    }
    $ttip = $t->st('tooltip_contributor_list');
    if($contributors !== '')
      $contributors = '<div id="contributorGroup" class="pull-right btn-group">'
                    . '<button class="btn btn-mini btn-info dropdown-toggle" data-toggle="dropdown" title="'.$ttip.'">'
                    . '<img src="img/people.png">'
                    . '<span class="caret"></span>'
                    . '</button>'
                    . '<table class="dropdown-menu table-hover table-condensed">'
                    . $contributors
                    . '</table></div>';
    //Composition:
    $languagePlayAll = $t->st('language_playAll');
    return "<div class='row-fluid'>"
         . "<div class='span3'>$prev</div>"
         . "<div class='span6'><h3 class='color-language noborders centertext'>$longName</h3></div>"
         . "<div class='span3'>$next</div>"
         . "</div>"
         . "<div class='row-fluid'>"
         . "<div class='span3'>"
         . "<i class='icon-eject rotate90' id='language_playAll' title='$languagePlayAll'></i>"
         . "</div>"
         . "<div id='languageHeadline' class='span6'>$links</div>"
         . "<div class='span3'>$contributors</div>"
         . "</div>"
         . "<table class='languageHeadlineParent'>"
         . "<tbody id='languageDescription'>$desc</tbody></table>";
  }
  /**
   * @param $language Language
   * */
  public function languageTable($language){
    $v = $this->valueManager;
    $t = $v->getTranslator();
    echo $this->languageHeadline($language);
    echo '<table id="languageTable" class="table table-bordered table-striped"><tbody><tr>';
    $width  = 6;
    $wCount = 0;
    foreach($v->getStudy()->getWords($v) as $w){
      if($wCount === $width){
        echo '</tr><tr>';
        $wCount = 0;
      }
      $wCount ++;
      $entry  = "<td class='transcription'>";
      $tr     = new TranscriptionFromWordLang($w, $language);
      if(!$tr->exists()) continue;
      $href   = $v->gpv()->setView('WordView')->setLanguages(array())->setWord($w)->link();
      $trans  = $w->getTranslation($v, true, false);
      $ttip   = $w->getLongName();
      $ttip   = (is_null($ttip)) ? '' : " title='$ttip'";
      $entry .= "<a class='tableLink color-word' $href$ttip>$trans</a><br />";
      if($spelling = $tr->getAltSpelling($v))
        $entry .= "<div class='altSpelling' >".$spelling.'</div>';
      $entry .= $tr->getPhonetic($v, true).'</td>';
      echo $entry;
    }
    echo "</tr></tbody></table>";
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
        $t = new TranscriptionFromWordLang($w, $l);
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
