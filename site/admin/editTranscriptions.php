<?php
require_once('common.php');
if(!session_validate($dbConnection))
  header('LOCATION: index.php');
if(!session_mayEdit($dbConnection))
  header('LOCATION: index.php');
?>
<!DOCTYPE HTML>
<html>
  <?php
    $title = 'Edit Transcriptions';
    $jsFiles = array('extern/jquery.dataTables.js', 'dataTables.js');
    require_once('head.php');
  ?>
  <body>
    <?php require_once('topmenu.php'); ?>
    <h3>&nbsp;&nbsp;For editing transcriptions choose a study:</h3>
<div id="ipaKeyboard" class="modal hide" data-backdrop="false" style="width:auto !important;height:auto !important">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">x</button>
    <span title="Clean Copy Area" onclick="$('.copyarea').text('')" style="cursor:pointer">⊗</span>
    <span title="Copy Area - Click into to select all" id="copyarea" class="copyarea" style="background-color:#ddd;margin-right:20px;padding:3px;border:1px solid black;font-family:Charis SIL" onclick="selectText(this.id)"></span>
    <span class="symbolDescription">&nbsp;</span>
  </div>
  <div class="modal-body" style="padding: 0px; margin: 5px;max-height: 700px !important;">
<div id="ipaConsonants">
<table class="table table-bordered table-condensed">
<tbody><tr>
<th></th>
<th colspan="2">Bilabial</th>
<th colspan="2">Labiodental</th>
<th colspan="2">Dental</th>
<th colspan="2">Alveolar</th>
<th colspan="2">Postalveolar</th>
<th colspan="2">Retroflex</th>
<th colspan="2">Palatal</th>
<th colspan="2">Velar</th>
<th colspan="2">Uvular</th>
<th colspan="2">Pharyngeal</th>
<th colspan="2">Glottal</th>
</tr>
<tr>
  <th>Plosive</th>
  <td class="hbr" unicode="p" description="U+0070 : Voiceless bilabial plosive - LATIN SMALL LETTER P">p</td>
  <td class="hbl" unicode="b" description="U+0062 : Voiced bilabial plosive - LATIN SMALL LETTER B">b</td>
  <td colspan="2"></td>
  <td class="hbr" colspan="2"></td>
  <td class="hbs" unicode="t" description="U+0074 : Voiceless alveolar plosive - LATIN SMALL LETTER T">t</td>
  <td class="hbs" unicode="d" description="U+0064 : Voiced alveolar plosive - LATIN SMALL LETTER D">d</td>
  <td class="hbl" colspan="2"></td>
  <td class="hbr" unicode="ʈ" description="U+0288 : Voiceless retroflex plosive - LATIN SMALL LETTER T WITH RETROFLEX HOOK">ʈ</td>
  <td class="hbl" unicode="ɖ" description="U+0256 : Voiced retroflex plosive - LATIN SMALL LETTER D WITH TAIL">ɖ</td>
  <td class="hbr" unicode="ɟ" description="U+025F : Voiced palatal plosive - LATIN SMALL LETTER DOTLESS J WITH STROKE">c</td>
  <td class="hbl" unicode="ɟ" description="U+025F : Voiced palatal plosive - LATIN SMALL LETTER DOTLESS J WITH STROKE">ɟ</td>
  <td class="hbr" unicode="k" description="U+006B : Voiceless velar plosive - LATIN SMALL LETTER K">k</td>
  <td class="hbl" unicode="ɡ" description="U+0261 : Voiced velar plosive - LATIN SMALL LETTER SCRIPT G">ɡ</td>
  <td class="hbr" unicode="q" description="U+0071 : Voiceless uvular plosive - LATIN SMALL LETTER Q">q</td>
  <td class="hbl" unicode="ɢ" description="U+0262 : Voiced uvular plosive - LATIN LETTER SMALL CAPITAL G">ɢ</td>
  <td></td><td class="impossible"></td>
  <td unicode="ʔ" description="U+0294 : Voiceless glottal plosive - LATIN LETTER GLOTTAL STOP">ʔ</td>
  <td class="impossible"></td>
</tr>
<tr>
  <th>Nasal</th>
  <td class="hbr"></td>
  <td class="hbl" unicode="m" description="U+006D : Voiced bilabial nasal - LATIN SMALL LETTER M">m</td>
  <td class="hbr"></td>
  <td class="hbl" unicode="ɱ" description="U+0271 : Voiced labiodental nasal - LATIN SMALL LETTER M WITH HOOK">ɱ</td>
  <td class="hbr" colspan="2"></td>
  <td class="hbs"></td>
  <td class="hbs" unicode="n" description="U+006E : Voiced alveolar nasal - LATIN SMALL LETTER N">n</td>
  <td class="hbl" colspan="2"></td>
  <td class="hbr"></td>
  <td class="hbl" unicode="ɳ" description="U+0273 : Voiced retroflex nasal - LATIN SMALL LETTER N WITH RETROFLEX HOOK">ɳ</td>
  <td class="hbr"></td>
  <td class="hbl" unicode="ɲ" description="U+0272 : Voiced palatal nasal - LATIN SMALL LETTER N WITH LEFT HOOK">ɲ</td>
  <td class="hbr"></td>
  <td class="hbl" unicode="ŋ" description="U+014B : Voiced velar nasal - LATIN SMALL LETTER ENG">ŋ</td>
  <td class="hbr"></td>
  <td class="hbl" unicode="ɴ" description="U+0274 : Voiced uvular nasal - LATIN LETTER SMALL CAPITAL N">ɴ</td>
  <td colspan="2" class="impossible"></td>
  <td colspan="2" class="impossible"></td>
</tr>
<tr>
  <th>Trill</th>
  <td class="hbr"></td>
  <td class="hbl" unicode="ʙ" description="U+0299 : Voiced bilabial trill - LATIN LETTER SMALL CAPITAL B">ʙ</td>
  <td colspan="2"></td>
  <td class="hbr" colspan="2"></td>
  <td class="hbs"></td>
  <td class="hbs" unicode="r" description="U+0072 : Voiced alveolar trill - LATIN SMALL LETTER R">r</td>
  <td class="hbl" colspan="2"></td>
  <td colspan="2"></td>
  <td colspan="2"></td>
  <td colspan="2" class="impossible"></td>
  <td class="hbr"></td>
  <td class="hbl" unicode="ʀ" description="U+0280 : Voiced uvular trill - LATIN LETTER SMALL CAPITAL R">ʀ</td>
  <td colspan="2"></td>
  <td colspan="2" class="impossible"></td>
</tr>
<tr>
  <th>Tap or Flap</th>
  <td colspan="2"></td>
  <td class="hbr"></td>
  <td class="hbl" unicode="ⱱ" description="U+2C71 : Voiced labiodental flap - LATIN SMALL LETTER V WITH RIGHT HOOK">ⱱ</td>
  <td class="hbr" colspan="2"></td>
  <td class="hbs"></td>
  <td class="hbs" unicode="ɾ" description="U+027E : Voiced alveolar flap - LATIN SMALL LETTER R WITH FISHHOOK">ɾ</td>
  <td class="hbl" colspan="2"></td>
  <td class="hbr"></td>
  <td class="hbl" unicode="ɽ" description="U+027D : Voiced retroflex flap - LATIN SMALL LETTER R WITH TAIL">ɽ</td>
  <td colspan="2"></td>
  <td colspan="2" class="impossible"></td>
  <td colspan="2"></td>
  <td colspan="2"></td>
  <td colspan="2" class="impossible"></td>
</tr>
<tr>
  <th>Fricative</th>
  <td class="hbr" unicode="ɸ" description="U+0278 : Voiceless bilabial fricative - LATIN SMALL LETTER PHI">ɸ</td>
  <td class="hbl" unicode="β" description="U+03B2 : Voiced bilabial fricative - GREEK SMALL LETTER BETA">β</td>
  <td class="hbr" unicode="f" description="U+0066 : Voiceless labiodental fricative - LATIN SMALL LETTER F">f</td>
  <td class="hbl" unicode="v" description="U+0076 : Voiced labiodental fricative - LATIN SMALL LETTER V">v</td>
  <td class="hbr" unicode="θ" description="U+03B8 : Voiceless dental fricative - GREEK SMALL LETTER THETA">θ</td>
  <td class="hbl" unicode="ð" description="U+00F0 : Voiced dental fricative - LATIN SMALL LETTER ETH">ð</td>
  <td class="hbr" unicode="s" description="U+0073 : Voiceless alveolar fricative - LATIN SMALL LETTER S">s</td>
  <td class="hbl" unicode="z" description="U+007A : Voiced alveolar fricative - LATIN SMALL LETTER Z">z</td>
  <td class="hbr" unicode="ʃ" description="U+0283 : Voiceless postalveolar fricative - LATIN SMALL LETTER ESH">ʃ</td>
  <td class="hbl" unicode="ʒ" description="U+0292 : Voiced postalveolar fricative - LATIN SMALL LETTER EZH">ʒ</td>
  <td class="hbr" unicode="ʂ" description="U+0282 : Voiceless retroflex fricative - LATIN SMALL LETTER S WITH HOOK">ʂ</td>
  <td class="hbl" unicode="ʐ" description="U+0290 : Voiced retroflex fricative - LATIN SMALL LETTER Z WITH RETROFLEX HOOK">ʐ</td>
  <td class="hbr" unicode="ç" description="U+00E7 : Voiceless palatal fricative - LATIN SMALL LETTER C WITH CEDILLA">ç</td>
  <td class="hbl" unicode="ʝ" description="U+029D : Voiceless palatal fricative - LATIN SMALL LETTER J WITH CROSSED-TAIL">ʝ</td>
  <td class="hbr" unicode="x" description="U+0078 : Voiceless velar fricative - LATIN SMALL LETTER X">x</td>
  <td class="hbl" unicode="ɣ" description="U+0263 : Voiced velar fricative - LATIN SMALL LETTER GAMMA">ɣ</td>
  <td class="hbr" unicode="χ" description="U+03C7 : Voiceless uvular fricative - GREEK SMALL LETTER CHI">χ</td>
  <td class="hbl" unicode="ʁ" description="U+0281 : Voiced uvular fricative - LATIN LETTER SMALL CAPITAL INVERTED R">ʁ</td>
  <td class="hbr" unicode="ħ" description="U+0127 : Voiceless pharyngeal fricative - LATIN SMALL LETTER H WITH STROKE">ħ</td>
  <td class="hbl" unicode="ʕ" description="U+0295 : Voiced pharyngeal fricative - LATIN LETTER PHARYNGEAL VOICED FRICATIVE">ʕ</td>
  <td class="hbr" unicode="h" description="U+0068 : Voiceless glottal fricative - LATIN SMALL LETTER H">h</td>
  <td class="hbl" unicode="ɦ" description="U+0266 : Voiced glottal fricative - LATIN SMALL LETTER H WITH HOOK">ɦ</td>
</tr>
<tr>
  <th>Lateral fricative</th>
  <td class="impossible" colspan="2"></td>
  <td class="impossible" colspan="2"></td>
  <td class="hbr" colspan="2"></td>
  <td class="hbs" unicode="ɬ" description="U+026C : Voiceless alveolar lateral fricative - LATIN SMALL LETTER L WITH BELT">ɬ</td>
  <td class="hbs" unicode="ɮ" description="U+026E : Voiced alveolar lateral fricative - LATIN SMALL LETTER LEZH">ɮ</td>
  <td class="hbl" colspan="2"></td>
  <td colspan="2"></td>
  <td colspan="2"></td>
  <td colspan="2"></td>
  <td colspan="2"></td>
  <td class="impossible" colspan="2"></td>
  <td class="impossible" colspan="2"></td>
</tr>
<tr>
  <th>Approximant</th>
  <td colspan="2"></td>
  <td class="hbr"></td>
  <td class="hbl" unicode="ʋ" description="U+028B : Voiced labiodental approximant - LATIN SMALL LETTER V WITH HOOK">ʋ</td>
  <td class="hbr" colspan="2"></td>
  <td class="hbs"></td>
  <td class="hbs" unicode="ɹ" description="U+0279 : Voiced alveolar approximant - LATIN SMALL LETTER TURNED R">ɹ</td>
  <td class="hbl" colspan="2"></td>
  <td class="hbr"></td>
  <td class="hbl" unicode="ɻ" description="U+027B : Voiced retroflex approximant - LATIN SMALL LETTER TURNED R WITH HOOK">ɻ</td>
  <td class="hbr"></td>
  <td class="hbl" unicode="j" description="U+006A : Voiced palatal approximant - LATIN SMALL LETTER J">j</td>
  <td class="hbr"></td>
  <td class="hbl" unicode="ɰ" description="U+0270 : Voiced velar approximant - LATIN SMALL LETTER TURNED M WITH LONG LEG">ɰ</td>
  <td colspan="2"></td>
  <td colspan="2"></td>
  <td colspan="2" class="impossible"></td>
</tr>
<tr>
  <th>Lateral approximant</th>
  <td colspan="2" class="impossible"></td>
  <td colspan="2" class="impossible"></td>
  <td class="hbr" colspan="2"></td>
  <td class="hbs"></td>
  <td class="hbs" unicode="l" description="U+006C : Voiced alveolar lateral approximant - LATIN SMALL LETTER L">l</td>
  <td class="hbl" colspan="2"></td>
  <td class="hbr"></td>
  <td class="hbl" unicode="ɭ" description="U+026D : Voiced retroflex lateral approximant - LATIN SMALL LETTER L WITH RETROFLEX HOOK">ɭ</td>
  <td class="hbr"></td>
  <td class="hbl" unicode="ʎ" description="U+028E : Voiced palatal lateral approximant - LATIN SMALL LETTER TURNED Y">ʎ</td>
  <td class="hbr"></td>
  <td class="hbl" unicode="ʟ" description="U+029F : Voiced velar lateral approximant - LATIN LETTER SMALL CAPITAL L">ʟ</td>
  <td colspan="2"></td>
  <td colspan="2" class="impossible"></td>
  <td class="impossible"></td>
  <td class="special" unicode="ɫ" description="U+026B : Voiced velarized/pharyngealized alveolar lateral approximant - LATIN SMALL LETTER L WITH MIDDLE TILDE">ɫ</td>
</tr>
</tbody></table>
</div>
<div id="ipaOthers" class="hide">
<div class="pull-left inline">
  <h4>Consonants (Non-Pulmonic)</h4>
  <table class="table table-bordered table-condensed"><tbody>
  <tr><th>Ejectives</th>
    <td unicode="ʼ" description="U+02BC : Ejective - MODIFIER LETTER APOSTROPHE">ʼ</td>
  </tr>
  <tr><th>Voiced implosives</th>
    <td unicode="ɓ" description="U+0253 : Voiced bilabial implosive - LATIN SMALL LETTER B WITH HOOK">ɓ</td>
    <td unicode="ɗ" description="U+0257 : Voiced alveolar implosive - LATIN SMALL LETTER D WITH HOOK">ɗ</td>
    <td unicode="ʄ" description="U+0284 : Voiced palatal implosive - LATIN SMALL LETTER DOTLESS J WITH STROKE AND HOOK">ʄ</td>
    <td unicode="ɠ" description="U+0260 : Voiced velar implosive - LATIN SMALL LETTER G WITH HOOK">ɠ</td>
    <td unicode="ʛ" description="U+029B : Voiced uvular implosive - LATIN LETTER SMALL CAPITAL G WITH HOOK">ʛ</td>
  </tr>
  <tr><th>Clicks</th>
    <td unicode="ʘ" description="U+0298 : Bilabial click - LATIN LETTER BILABIAL CLICK">ʘ</td>
    <td unicode="ǀ" description="U+01C0 : Dental click - LATIN LETTER DENTAL CLICK">ǀ</td>
    <td unicode="ǃ" description="U+01C3 : (Post)alveolar click - LATIN LETTER RETROFLEX CLICK">ǃ</td>
    <td unicode="ǂ" description="U+01C2 : Palatoalveolar click - LATIN LETTER ALVEOLAR CLICK">ǂ</td>
    <td unicode="ǁ" description="U+01C1 : Alveolar lateral click - LATIN LETTER LATERAL CLICK">ǁ</td>
  </tr>
  </tbody></table>
</div>
<div class="pull-left inline" style="margin-left:100px !important">
  <h3>Others</h3>
  <table class="table table-bordered table-condensed">
  <tbody><tr>
    <td unicode="ʍ" description="U+028D : Voiceless labial-velar fricative - LATIN SMALL LETTER TURNED W">ʍ</td>
    <td unicode="ʡ" description="U+02A1 : Epiglottal plosive - LATIN LETTER GLOTTAL STOP WITH STROKE">ʡ</td>
    <td unicode="ʬ" description="U+02AC : Bilabial percussive - LATIN LETTER BILABIAL PERCUSSIVE">ʬ</td>
    <td unicode="¡" description="U+00A1 : Sublaminal lower alveolar percussive click - INVERTED EXCLAMATION MARK">¡</td>
    <td unicode="w" description="U+0077 : Voiced labial-velar approximant - LATIN SMALL LETTER W">w</td>
    <td unicode="ɕ" description="U+0255 : Voiceless alveolo-palatal fricative - LATIN SMALL LETTER C WITH CURL">ɕ</td>
  </tr><tr>
    <td unicode="ʭ" description="U+02AD : Bidental percussive - LATIN LETTER BIDENTAL PERCUSSIVE">ʭ</td>
    <td unicode="ǃ¡" description="Alveolar and sublaminal click (cluck-click)">ǃ¡</td>
    <td unicode="ɥ" description="U+0265 : Voiced labial-palatal approximant - LATIN SMALL LETTER TURNED H">ɥ</td>
    <td unicode="ʑ" description="U+0291 : Voiced alveolo-palatal fricative - LATIN SMALL LETTER Z WITH CURL">ʑ</td>
    <td unicode="ʪ" description="U+02AA : Voiceless lateralized alveolar fricative - LATIN SMALL LETTER LS DIGRAPH">ʪ</td>
    <td unicode="ʜ" description="U+029C : Voiceless epiglottal fricative - LATIN LETTER SMALL CAPITAL H">ʜ</td>
  </tr><tr>
    <td unicode="ɺ" description="U+027A : Voiced alveolar lateral flap - LATIN SMALL LETTER TURNED R WITH LONG LEG">ɺ</td>
    <td unicode="ʫ" description="U+02AB : Voiced lateralized alveolar fricative - LATIN SMALL LETTER LZ DIGRAPH">ʫ</td>
    <td unicode="ʢ" description="U+02A2 : Voiced epiglottal fricative - LATIN LETTER REVERSED GLOTTAL STOP WITH STROKE">ʢ</td>
    <td unicode="ɧ" description="U+0267 : Voiceless simultaneous postalveolar and velar fricative - LATIN SMALL LETTER HENG WITH HOOK">ɧ</td>
    <td unicode="ʩ" description="U+02A9 : Velopharyngeal fricative - LATIN SMALL LETTER FENG DIGRAPH">ʩ</td>
    <td></td>
  </tr>
  </tbody></table>
</div>
<div class="pull-left inline">
  <h3>Diacritics</h3>
  <table class="table table-bordered table-condensed">
    <tbody><tr>
      <td description="U+0329 : Syllabic - COMBINING VERTICAL LINE BELOW" unicode="̩">◌̩ Syllabic</td>
      <td colspan="2"></td>
      <td description="U+02B0 : Aspirated - MODIFIER LETTER SMALL H" unicode="ʰ">◌ʰ Aspirated</td>
      <td description="U+20ED : Unaspirated - MODIFIER LETTER UNASPIRATED" unicode="⃭">◌˭ Unaspirated</td>
      <td description="U+02BC : Ejective - MODIFIER LETTER APOSTROPHE" unicode="ʼ">◌ʼ Ejective</td>
      <td description="U+0361 : Affricate or double articulation - COMBINING DOUBLE INVERTED BREVE" unicode="͡">◌͡ Affricate or double articulation</td>
      <td description="U+035C : Affricate or double articulation - COMBINING DOUBLE BREVE BELOW" unicode="͜">◌͜ Affricate or double articulation</td>
    </tr><tr>
      <td description="U+0303 : Nasalized - COMBINING TILDE" unicode="̃">◌̃ Nasalized</td>
      <td description="U+02B7 : Labialized - MODIFIER LETTER SMALL W" unicode="ʷ">◌ʷ Labialized</td>
      <td description="U+02B2 : Palatalized - MODIFIER LETTER SMALL J" unicode="ʲ">◌ʲ Palatalized</td>
      <td description="U+02E0 : Velarized - MODIFIER LETTER SMALL GAMMA" unicode="ˠ">◌ˠ Velarized</td>
      <td description="U+0334 : Velarized or pharyngealized - COMBINING TILDE OVERLAY" unicode="̴">◌̴ Velarized or pharyngealized</td>
      <td description="U+02E4 : Pharyngealized - MODIFIER LETTER SMALL REVERSED GLOTTAL STOP" unicode="ˤ">◌ˤ Pharyngealized</td>
      <td colspan="2"></td>
    </tr><tr>
      <td description="U+0325 : Voiceless - COMBINING RING BELOW" unicode="̥">◌̥ Voiceless</td>
      <td description="U+02EC : Voicing - MODIFIER LETTER VOICING" unicode="ˬ">ˬ Voicing</td>
      <td description="U+032C : Voiced - COMBINING CARON BELOW" unicode="̬">◌̬ Voiced</td>
      <td description="U+0324 : Breathy voiced - COMBINING DIAERESIS BELOW" unicode="̤">◌̤ Breathy voiced</td>
      <td description="U+0330 : Creaky voiced - COMBINING TILDE BELOW" unicode="̰">◌̰ Creaky voiced</td>
      <td colspan="3"></td>
    </tr><tr>
      <td description="U+02D4 : Raised - MODIFIER LETTER UP TACK" unicode="˔">◌˔ Raised</td>
      <td description="U+031D : Raised - COMBINING UP TACK BELOW" unicode="̝">◌̝ Raised</td>
      <td description="U+02D5 : Lowered - MODIFIER LETTER DOWN TACK" unicode="˕">◌˕ Lowered</td>
      <td description="U+031E : Lowered - COMBINING DOWN TACK BELOW" unicode="̞">◌̞ Lowered</td>
      <td></td>
      <td description="U+0346 : Dentolabial - COMBINING BRIDGE ABOVE" unicode="͆">◌͆ Dentolabial</td>
      <td description="U+033C : Linguolabial - COMBINING SEAGULL BELOW" unicode="̼">◌̼ Linguolabial</td>
      <td description="U+034D : Labial spreading - COMBINING LEFT RIGHT ARROW BELOW" unicode="͍">◌͍ Labial spreading</td>
    </tr><tr>
      <td description="U+032A : Dental - COMBINING BRIDGE BELOW" unicode="̪">◌̪ Dental</td>
      <td description="U+0347 : Alveolar - COMBINING EQUALS SIGN BELOW" unicode="͇">◌͇ Alveolar</td>
      <td description="U+033A : Apical - COMBINING INVERTED BRIDGE BELOW" unicode="̺">◌̺ Apical</td>
      <td description="U+033B : Laminal - COMBINING SQUARE BELOW" unicode="̻">◌̻ Laminal</td>
      <td description="Interdental/bidental" unicode="◌̪͆">◌̪͆ Interdental/bidental</td>
      <td colspan="3"></td>
    </tr><tr>
      <td description="U+031A : No audible release - COMBINING LEFT ANGLE ABOVE" unicode="̚">◌̚ No audible release</td>
      <td description="U+02E1 : Lateral release - MODIFIER LETTER SMALL L" unicode="ˡ">◌ˡ Lateral release</td>
      <td description="U+207F : Nasal release - SUPERSCRIPT LATIN SMALL LETTER N" unicode="ⁿ">◌ⁿ Nasal release</td>
      <td description="U+034B : Nasal escape - COMBINING HOMOTHETIC ABOVE" unicode="͋">◌͋ Nasal escape</td>
      <td description="U+034A : Denasal - COMBINING NOT TILDE ABOVE" unicode="͊">◌͊ Denasal</td>
      <td description="U+034C : Velopharyngeal friction - COMBINING ALMOST EQUAL TO ABOVE" unicode="͌">◌͌ Velopharyngeal friction</td>
      <td colspan="2"></td>
        <!-- <td description="U+0348 : Strong articulation - COMBINING DOUBLE VERTICAL LINE BELOW" unicode="͈">◌͈ Strong articulation</td>
        <td description="U+0349 : Weak articulation - COMBINING LEFT ANGLE BELOW" unicode="͉">◌͉ Weak articulation</td>
        <td description="U+034E : Whistled articulation - COMBINING UPWARDS ARROW BELOW" unicode="͎">◌͎ Whistled articulation</td>
        <td description="U+005C : Reiterated articulation - REVERSE SOLIDUS" unicode="\">\ Reiterated articulation</td>
        <td description="U+0362 : Sliding articulation - COMBINING DOUBLE RIGHTWARDS ARROW BELOW" unicode="͢">◌͢ Sliding articulation</td>
        <td description="U+208D : Initial partial - SUBSCRIPT LEFT PARENTHESIS" unicode="₍">₍◌ Initial partial</td>
        <td description="U+208E : Final partial - SUBSCRIPT RIGHT PARENTHESIS" unicode="₎">◌₎ Final partial</td> -->
    </tr>
  </tbody></table>
</div>
</div>
<div id="ipaTone" class="hide">
<h3>Tone</h3>
<table class="table table-bordered table-condensed">
<tbody><tr>
<th colspan="8">Tones and Word Accents</th>
</tr>
<tr>
  <td description="U+030B : Extra high - COMBINING DOUBLE ACUTE ACCENT" unicode="̋" colspan="2">◌̋</td>
  <td description="U+02E5 : Extra high - MODIFIER LETTER EXTRA-HIGH TONE BAR" unicode="˥" colspan="2">˥</td>
  <td description="U+030C : Rising - COMBINING CARON" unicode="̌" colspan="2">◌̌</td>
  <td description="U+2193 : Downstep (ExtIPA: ingressive airflow) - DOWNWARDS ARROW" unicode="↓" colspan="2">↓</td>  
</tr>
<tr>
  <td description="U+0301 : High - COMBINING ACUTE ACCENT" unicode="́" colspan="2">◌́</td>
  <td description="U+02E6 : High - MODIFIER LETTER HIGH TONE BAR" unicode="˦" colspan="2">˦</td>
  <td description="U+0302 : Falling - COMBINING CIRCUMFLEX ACCENT" unicode="̂" colspan="2">◌̂</td>
  <td description="U+2191 : Upstep (ExtIPA: egressive airflow) - UPWARDS ARROW" unicode="↑" colspan="2">↑</td>
</tr>
<tr>
  <td description="U+0304 : Mid - COMBINING MACRON" unicode="̄" colspan="2">◌̄</td>
  <td description="U+02E7 : Mid - MODIFIER LETTER MID TONE BAR" unicode="˧" colspan="2">˧</td>
  <td description="U+1DC4 : High rising - COMBINING MACRON-ACUTE" unicode="᷄" colspan="2">◌᷄</td>
  <td description="U+2197 : Global rise - NORTH EAST ARROW" unicode="↗" colspan="2">↗</td>
</tr>
<tr>
  <td description="U+0300 : Low - COMBINING GRAVE ACCENT" unicode="̀" colspan="2">◌̀</td>
  <td description="U+02E8 : Low - MODIFIER LETTER LOW TONE BAR" unicode="˨" colspan="2">˨</td>
  <td description="U+1DC5 : Low rising - COMBINING GRAVE-MACRON" unicode="᷅" colspan="2">◌᷅</td>
  <td description="U+2198 : Global fall - SOUTH EAST ARROW" unicode="↘" colspan="2">↘</td>
</tr>
<tr>
  <td description="U+030F : Extra low - COMBINING DOUBLE GRAVE ACCENT" unicode="̏" colspan="2">◌̏</td>
  <td description="U+02E9 : Extra low - MODIFIER LETTER EXTRA-LOW TONE BAR" unicode="˩" colspan="2">˩</td>
  <td description="U+1DC8 : Rising-falling - COMBINING GRAVE-ACUTE-GRAVE" unicode="᷈" colspan="2">◌᷈</td>
  <td class="spacer"></td>
</tr>
</tbody></table>
</div>
<div id="ipaVowels" class="hide">
<div class="inline">
<table><tr><td>
<h3>Vowels</h3>
<table class="table table-bordered table-condensed"><tbody>
  <tr>
    <th></th>
    <th colspan="2">Front</th>
    <th colspan="2"></th>
    <th colspan="2">Central</th>
    <th colspan="2"></th>
    <th colspan="2">Back</th>
  </tr>
  <tr>
    <td>Close</td>
    <td description="U+0069 : Close front unrounded vowel - LATIN SMALL LETTER I" unicode="i">i</td>
    <td description="U+0079 : Close front rounded vowel - LATIN SMALL LETTER Y" unicode="y">y</td>
    <td colspan="2"></td>
    <td description="U+0268 : Close central unrounded vowel - LATIN SMALL LETTER I WITH STROKE" unicode="ɨ">ɨ</td>
    <td description="U+0289 : Close central rounded vowel - LATIN SMALL LETTER U BAR" unicode="ʉ">ʉ</td>
    <td colspan="2"></td>
    <td description="U+026F : Close back unrounded vowel - LATIN SMALL LETTER TURNED M" unicode="ɯ">ɯ</td>
    <td description="U+0075 : Close back rounded vowel - LATIN SMALL LETTER U" unicode="u">u</td>
  </tr>
  <tr>
    <td></td><td></td>
    <td description="U+026A : Near-close near-front unrounded vowel - LATIN LETTER SMALL CAPITAL I" unicode="ɪ">ɪ</td>
    <td description="U+028F : Near-close near-front rounded vowel - LATIN LETTER SMALL CAPITAL Y" unicode="ʏ">ʏ</td>
    <td colspan="4"></td>
    <td description="U+028A : Near-close near-back rounded vowel - LATIN SMALL LETTER UPSILON" unicode="ʊ">ʊ</td>  
    <td colspan="2"></td>
  </tr>
  <tr>
    <td>Close-mid</td>
    <td></td>
    <td description="U+0065 : Close-mid front unrounded vowel - LATIN SMALL LETTER E" unicode="e">e</td>
    <td description="U+00F8 : Close-mid front rounded vowel - LATIN SMALL LETTER O WITH STROKE" unicode="ø">ø</td>
    <td></td>
    <td description="U+0258 : Close-mid central unrounded vowel - LATIN SMALL LETTER REVERSED E" unicode="ɘ">ɘ</td>
    <td description="U+0275 : Close-mid central rounded vowel - LATIN SMALL LETTER BARRED O" unicode="ɵ">ɵ</td>
    <td colspan="2"></td>
    <td description="U+0264 : Close-mid back unrounded vowel - LATIN SMALL LETTER RAMS HORN" unicode="ɤ">ɤ</td>
    <td description="U+006F : Close-mid back rounded vowel - LATIN SMALL LETTER O" unicode="o">o</td>
  </tr>
  <tr>
    <td></td>
    <td colspan="4"></td>
    <td colspan="2" description="U+0259 : Mid central vowel - LATIN SMALL LETTER SCHWA" unicode="ə">ə</td>
    <td colspan="4"></td>
  </tr>
  <tr>
    <td>Open-mid</td>
    <td colspan="2"></td>
    <td description="U+025B : Open-mid front unrounded vowel - LATIN SMALL LETTER OPEN E" unicode="ɛ">ɛ</td>
    <td description="U+0153 : Open-mid front rounded vowel - LATIN SMALL LIGATURE OE" unicode="œ">œ</td>
    <td></td>
    <td description="U+025C : Open-mid central unrounded vowel - LATIN SMALL LETTER REVERSED OPEN E" unicode="ɜ">ɜ</td>
    <td description="U+025E : Open-mid central rounded vowel - LATIN SMALL LETTER CLOSED REVERSED OPEN E" unicode="ɞ">ɞ</td>
    <td></td>
    <td description="U+028C : Open-mid back unrounded vowel - LATIN SMALL LETTER TURNED V" unicode="ʌ">ʌ</td>
    <td description="U+0254 : Open-mid back rounded vowel - LATIN SMALL LETTER OPEN O" unicode="ɔ">ɔ</td>
  </tr>
  <tr>
    <td></td>
    <td colspan="2"></td>
    <td colspan="2" description="U+00E6 : Near-open front unrounded vowel - LATIN SMALL LETTER AE" unicode="æ">æ</td>
    <td></td>
    <td colspan="2" description="U+0250 : Near-open central vowel - LATIN SMALL LETTER TURNED A" unicode="ɐ">ɐ</td>
    <td colspan="3"></td>
  </tr>
  <tr>
    <td>Open</td>
    <td colspan="3"></td>
    <td description="U+0061 : Open front unrounded vowel - LATIN SMALL LETTER A" unicode="a">a</td>
    <td description="U+0276 : Open front rounded vowel - LATIN LETTER SMALL CAPITAL OE" unicode="ɶ">ɶ</td>
    <td colspan="3"></td>
    <td description="U+0251 : Open back unrounded vowel - LATIN SMALL LETTER ALPHA" unicode="ɑ">ɑ</td>
    <td description="U+0252 : Open back rounded vowel - LATIN SMALL LETTER TURNED ALPHA" unicode="ɒ">ɒ</td>
  </tr>
</tbody></table>
<br>
<table class="table table-bordered table-condensed">
<tbody><tr><th colspan="9">Suprasegmentals</th>
</tr><tr>
  <td description="U+02C8 : Primary stress - MODIFIER LETTER VERTICAL LINE" unicode="ˈ">ˈ</td>
  <td description="U+02CC : Secondary stress - MODIFIER LETTER LOW VERTICAL LINE" unicode="ˌ">ˌ</td>
  <td description="U+02D0 : Long - MODIFIER LETTER TRIANGULAR COLON" unicode="ː">&nbsp;&nbsp;◌ː&nbsp;&nbsp;</td>
  <td description="U+02D1 : Half-long - MODIFIER LETTER HALF TRIANGULAR COLON" unicode="ˑ">◌ˑ</td>
  <td description="U+0306 : Extra-short - COMBINING BREVE" unicode="̆">◌̆</td>
  <td description="U+007C : Minor (foot) group - VERTICAL LINE" unicode="|">|</td>
  <td description="U+2016 : Major (intonation) group - DOUBLE VERTICAL LINE" unicode="‖">‖</td>
  <td description="U+002E : Syllable break - FULL STOP" unicode=".">.</td>
  <td description="U+203F : Linking (absence of a break) - UNDERTIE" unicode="‿">‿</td>
</tr>
</tbody></table>
</td>
<td valign="top" style="padding-left:20px">
  <h3>Diacritics</h3>
  <table class="table table-bordered table-condensed"><tbody>
  <tr>
    <td description="U+0303 : Nasalized - COMBINING TILDE" unicode="̃">◌̃ Nasalized</td>
    <td colspan="2"></td>
    <td description="U+02DE : Rhoticity - MODIFIER LETTER RHOTIC HOOK" unicode="˞">◌˞ Rhoticity</td>
    <td description="U+025A : Mid central vowel with rhoticity - LATIN SMALL LETTER SCHWA WITH HOOK" unicode="ɚ">ɚ</td>
    <td description="U+025D : Open-mid central unrounded vowel with rhoticity - LATIN SMALL LETTER REVERSED OPEN E WITH HOOK" unicode="ɝ">ɝ</td>    
  </tr><tr>
    <td description="U+02D4 : Raised - MODIFIER LETTER UP TACK" unicode="˔">◌˔ Raised</td>
    <td description="U+031D : Raised - COMBINING UP TACK BELOW" unicode="̝">◌̝ Raised</td>
    <td description="U+02D5 : Lowered - MODIFIER LETTER DOWN TACK" unicode="˕">◌˕ Lowered</td>
    <td description="U+031E : Lowered - COMBINING DOWN TACK BELOW" unicode="̞">◌̞ Lowered</td>
    <td colspan="2"></td>
  </tr><tr>
    <td description="U+031F : Advanced - COMBINING PLUS SIGN BELOW" unicode="̟">◌̟ Advanced</td>
    <td description="U+0320 : Retracted - COMBINING MINUS SIGN BELOW" unicode="̠">◌̠ Retracted</td>
    <td colspan="2"></td>
    <td description="U+0308 : Centralized - COMBINING DIAERESIS" unicode="̈">◌̈ Centralized</td>
    <td description="U+033D : Mid-centralized - COMBINING X ABOVE" unicode="̽">◌̽ Mid-centralized</td>
  </tr><tr>
    <td description="U+0339 : More rounded - COMBINING RIGHT HALF RING BELOW" unicode="̹">◌̹ More rounded</td>      
    <td description="U+031C : Less rounded - COMBINING LEFT HALF RING BELOW" unicode="̜">◌̜ Less rounded</td>
    <td></td>
    <td description="U+0325 : Voiceless - COMBINING RING BELOW" unicode="̥">◌̥ Voiceless</td>
    <td description="U+0324 : Breathy voiced - COMBINING DIAERESIS BELOW" unicode="̤">◌̤ Breathy voiced</td>
    <td description="U+0330 : Creaky voiced - COMBINING TILDE BELOW" unicode="̰">◌̰ Creaky voiced</td>
  </tr>
</tbody></table>
</td></tr></table>
</div>
</div>
  </div>
  <div class="modal-footer">
    <button data-target="#ipaConsonants" class="btn disabled">Consonants Main</button>
    <button data-target="#ipaOthers" class="btn">Consonants Other</button>
    <button data-target="#ipaVowels" class="btn">Vowels</button>
    <button data-target="#ipaTone" class="btn">Tone</button>
  </div>
</div>
    <div class="btn-group">
    <?php
      $current = isset($_GET['study']) ? $_GET['study'] : '';
      foreach(DataProvider::getStudies() as $s){
        $style = ($s === $current) ? ' btn-inverse' : '';
        echo "<a class=\"btn$style\" href=\"?study=$s\">$s</a>";
      }
    ?>
    </div>
    <div style="margin:30px;">
      <table class="display table table-bordered" style="width:90%">
      <?php
      $head = '<tr>'
                .'<th><input type="button" value="Save all" class="btn-small btn-primary saveAll" style="margin-top:-8px">&nbsp;<a href="#ipaKeyboard" data-toggle="modal" id="IPAOpenKeyboard" class="superscript" title="Open IPA Keyboard">ɚ</a>&nbsp;Phonetic <small>as regex</small></th>'
                // .'<th>SplAlt1</th>'
                .'<th>Word</th>'
                .'<th>Short Name</th>'
                .'<th>LanguageIx - FilePathPart</th>'
             .'</tr>';
      echo "<thead>$head</thead>";
      DataProvider::transcriptionTable($_GET['study']);
      foreach(DataProvider::$transcriptionTable as $t){
        echo "<tr data-transcrid='".$t['transcrid']."' data-study='".$_GET['study']."'>";
        echo "<td><a class='btn btn-small save' style='margin-top:-11px'><i title='Save' class='icon-hdd'></i></a><span class='hide'>".$t['Phonetic']."</span><input data-field='Phonetic' class='Phonetic' type='text' value='".$t['Phonetic']."' style='width:150px;font-family:Charis SIL;'></td>";
        // echo "<td><span class='searchval hide'>".$t['SpellingAltv1']."</span><input data-field='SpellingAltv1' class='SpellingAltv1' type='text' value='".$t['SpellingAltv1']."' style='width:150px;'></td>";
        echo "<td>".$t['Word']."</td>";
        echo "<td>".$t['ShortName']."</td>";
        echo "<td>".$t['LgIxFPP']."</td>";
        echo "</tr>";
      }
      ?>
      </table>
      <script type="application/javascript">
        function selectText(id){
            var sel, range;
            var el = document.getElementById(id);
            if (window.getSelection && document.createRange) {
              sel = window.getSelection();
              if(sel.toString() == ''){ //no text selection
                 window.setTimeout(function(){
                    range = document.createRange(); //range object
                    range.selectNodeContents(el); //sets Range
                    sel.removeAllRanges(); //remove all ranges from selection
                    sel.addRange(range);//add Range to a Selection.
                },1);
              }
            }else if (document.selection) { //older ie
                sel = document.selection.createRange();
                if(sel.text == ''){ //no text selection
                    range = document.body.createTextRange();
                    range.moveToElementText(el);//sets Range
                    range.select(); //make selection.
                }
            }
        }
        $(document).ready(function(){
          function insert(symbol){
              $('.copyarea').text($('.copyarea').text() + symbol);
          }
          function footerButton(button, buttons){
                //Changing the buttons:
                buttons.removeClass('disabled');
                button.addClass('disabled');
                //Changing the tables:
                this.$('.modal-body > div:visible').addClass('hide');
                this.$(button.data('target')).removeClass('hide')
                var t = this.$(button.data('target')).parent().parent();
                t.css('width', 'auto !important');
                t.css('height', 'auto !important');
                t.css('right', '');
                t.css('left', '');
                var ww = $(window).width();
                var rw = 0.17*ww;
                var ew = t.width();
                var x = 0;
                if((ww-ew-rw)>100) {
                  x = ww-ew-rw;
                  t.css('left', x + "px !important");
                  t.css('right', rw + "px !important");
                } else {
                  x = ww-rw-100;
                  t.width(x);
                  t.css('left', "80px !important");
                  t.css('right', rw + "px !important");
                }
          };
          $('td[unicode]').click(function(){/* Inserting on click */
            var symbol = $(this).attr('unicode');
            if(symbol){
              insert(symbol);
            }
          }).mouseout(function(){/* Clearing the footer after mouseout */
            $('.modal-header > .symbolDescription').html('&nbsp;');
          }).mouseover(function(){/* Displaying description on mouseover */
            var description = $(this).attr('description');
            $('.modal-header > .symbolDescription').text(description);
          });
          var buttons = $('.modal-footer > button').click(function(e){
                  footerButton($(e.target), buttons)});
          var table = $('table.display').DataTable({paging: true, ordering: false});
          $('table.display thead th').each( function () {
            var title = $(this).text();
            $(this).html($(this).html()+'<br /><input type="text" placeholder="Search..." />' );
          } );
          table.columns().every( function () {
            var that = this;
            $( 'input', this.header() ).on( 'keyup change', function () {
              if ( that.search() !== this.value ) {
                that.search( this.value, true, false, true ).draw();
              }
            });
          } );
          table.on('change', 'input.Phonetic', function(){$(this).changeInRow();});
          table.on('change', 'input.SpellingAltv1', function(){$(this).changeInRow();});
          table.on('click', '.btn-small.saveAll', function(){
            table.$('.btn.save.btn-warning').trigger('click');
          });
          table.on('click', '.btn.save', function(){
            var btn = $(this);
            if(!btn.hasClass('btn-warning')) return;
            btn.removeClass('btn-warning').addClass('btn-danger');
            tr = btn.closest('tr');
            var q  = {
              action: 'update'
            , Table: 'Transcriptions_'
            , Transid: tr.data('transcrid')
            , Study: tr.data('study')
            };
            var fields = {};
            tr.find('td').find('input').each(function() {
              fields[$(this).data('field')] = this.value;
            });
            q['Fields'] = fields;
            $.get('query/updateDB.php', q, function(ret){
              if(ret !== ""){
                alert(ret);
              }else{
                tr.find('td').each(function() {
                  var s = null;
                  $(this).find('input').each(function() {s=this.value});
                  if(s){
                    $(this).find('span.hide').each(function() {$(this).text(s)});
                  }
                });
                table.row(tr).invalidate();
                table.draw();
                btn.removeClass('btn-danger').addClass('btn-success');
              }
            });
          });
        });
      </script>
    </div>
    <iframe name="iframe_post_form" id="iframe_post_form" style="border:none;"></iframe>
  </body>
</html>
