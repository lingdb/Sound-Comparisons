/**
  Adds an inputform to allow filtering the wordlist for particles of sundust.
*/
function wordlistfilter(){
  //Function to enhance input with ipaSymbols:
  function enhanceIPA(input){
    var vMust  = 'iyɨʉɯuɪʏʊeøɘɵɤoəɛœɜɞʌɔæɐaɶɑɒɚɝ'; // All from vowels section
    var vMay   = '˥˦˧˨˩↓↑↗↘̋́̄̀̏᷈᷅᷄̂̌ˈˌːˑ̆|‖.‿̃˔̟̹̜̠̝˕˞̞̰̤̥̈̽';           // All from tone
    var cMain  = 'pbtdʈɖɟɟkɡqɢʔmɱnɳɲŋɴʙrʀⱱɾɽɸβfvθðszʃʒʂʐçʝxɣχʁħʕhɦɬɮʋɹɻjɰlɭʎʟɫ'; // All from consonants main
    var cOther = 'ʼɓɗʄɠʛʘǀǃǂǁʍʡʬ¡wɕʭǃ¡ɥʑʪʜɺʫʢɧʩ'; // All from consonants other 
    var cAdditional = 'ː'; // ː from vowels
    var cNasal  = 'mɱnɳɲŋɴ'; // From consonants all in nasal row.
    var vNasal  = '̃'; // ~ from vowels nasalised
    var fMain   = 'ɸβfvθðszʃʒʂʐçʝxɣχʁħʕhɦɬɮ'; // [Lateral-]Fricative from consonants main
    var fOther  = 'ɕʑɧ'; // Selection from consonants other
    var stMain  = 'pbtdʈɖɟɟkɡqɢʔ'; // All in plosive from consonants main
    var stOther = 'ɓɗʄɠʛʘǀǃǂǁ'; // All in voiced, imposives and clicks from consonants other
    //Replacing the input:
    input = input.replace(/V/g,     '[' + vMust + '][' + vMay + ']*'); // vowels
    input = input.replace(/C/g,     '[' + cMain + cOther + cAdditional + ']'); // consonants
    input = input.replace(/N/g,     '[' + cNasal + vNasal + ']'); // nasal
    input = input.replace(/FL/g,    '[ɬɮʋɹɻjɰ]'); // All from lateral {friccative, approx.}
    input = input.replace(/R/g,     '[ʋɹɻʀʁχrɾⱱ˞]'); // Selection by Paul
    input = input.replace(/A/g,     '(tʃ|ts|tθ|ʈʂ|dʒ|dz|dð|ɖʐ|pf|bβ|kx|ɡɣ)'); // affricate
    input = input.replace(/F/g,     '[' + fMain + fOther + ']'); // fricative, lateral friccative
    input = input.replace(/(S|T)/g, '[' + stMain + stOther + ']');
    input = input.toLowerCase();
    return input;
  }
  //Restoring the cookies:
  if(window.App.studyWatcher.studyChanged() || window.App.viewWatcher.viewChanged()){
    $.cookie('wordlistfilter_content',    null);
    $.cookie('wordlistfilter_inputId',    null);
    $.cookie('wordlistfilter_selectedId', null);
  }else{
    $($.cookie('wordlistfilter_selectedId')).addClass('selected');
    $($.cookie('wordlistfilter_inputId')).val($.cookie('wordlistfilter_content'));
  }
  //Clearing things on empty input:
  function chk(input){
    if(input != '')
      return;
    $('#FilterSpelling').removeClass('selected');
    $('#FilterPhonetic').removeClass('selected');
    $.cookie('wordlistfilter_selectedId', null);
    $.cookie('wordlistfilter_inputId', null);
    $.cookie('wordlistfilter_content', null);
  }
  //Check to see if we can filter in the languageTable aswell:
  var hasLanguageTable = ($('#languageTable').length > 0);
  //Function to fetch a filterSet from the LanguageTable
  var getLanguageTableSet = function(useTranscriptions){
    //If ¬useTranscriptions we use words to filter on.
    var set = $('#languageTable td.transcription').map(function(i, e){
      var ret = {target: $(e)};
      if(useTranscriptions){
        ret.text = $('div.transcription', e).text();
      }else{
        ret.text = $('.color-word', e).text();
      }
      return ret;
    });
    return set;
  };
  //Updating count of filtered words:
  function updateCount(){
    var c = $('ul.wordList li:visible').size();
    $('#FilterFoundMultiWords').text(c);
    if(c === 0){
      var i = $('#PhoneticFilter')
      if(i.val() === '')
        i = $('#SpellingFilter');
      if(i.val() === '')
        return;
      i.addClass('filterempty');
    }else
      $('#PhoneticFilter, #SpellingFilter').removeClass('filterempty');
  }
  //The magic filter function:
  function filter(set, input){
    //General rewriting of input:
    input = input.replace(/^#/, '^');
    input = input.replace(/#$/, '$');
    //Filtering the set against the input:
    $(set).each(function(i, e){
      var word = e.text.toLowerCase();
      if(word.search(input) >= 0)
        e.target.show();
      else
        e.target.hide();
    });
    updateCount();
  };
  //The Spellingfilter:
  function spellingFilter(){
    $('#PhoneticFilter').val('');
    $('#FilterPhonetic').removeClass('selected');
    $('#FilterSpelling').addClass('selected');
    var input = $('#SpellingFilter').val().toLowerCase();
    $.cookie('wordlistfilter_selectedId', '#FilterSpelling');
    $.cookie('wordlistfilter_inputId', '#SpellingFilter');
    $.cookie('wordlistfilter_content', input);
    var elems = $('ul.wordList .color-word').map(function(i, e){
      return {text: $(e).text(), target: $(e).closest('li')};
    });
    filter(elems, input);
    if(hasLanguageTable){
      filter(getLanguageTableSet(false), input);
      $('#languageTable').trigger('redraw');
    }
    chk(input);
  }
  $('#SpellingFilter').keyup(spellingFilter);
  //The Phoneticfilter:
  function phoneticFilter(){
    $('#SpellingFilter').val('');
    $('#FilterSpelling').removeClass('selected');
    $('#FilterPhonetic').addClass('selected');
    var input = $('#PhoneticFilter').val();
    $.cookie('wordlistfilter_selectedId', '#FilterPhonetic');
    $.cookie('wordlistfilter_inputId', '#PhoneticFilter');
    $.cookie('wordlistfilter_content', input);
    var elems = $('ul.wordList .p50:nth-child(2)').map(function(i, e){
      var s = $(e).text();
      s = s.match(/\[(.*)\]/)[1];
      return {text: s, target: $(e).closest('li')};
    });
    filter(elems, enhanceIPA(input));
    if(hasLanguageTable){
      filter(getLanguageTableSet(true), input);
      $('#languageTable').trigger('redraw');
    }
    chk(input);
  }
  $('#PhoneticFilter').keyup(phoneticFilter);
  //The magical add-all-button:
  $('#FilterAddMultiWords').click(function(){
    var newWords = '';
    $('ul.wordList li:visible').each(function(){
      if($(this).parent().hasClass('selected'))
        return;
      newWords += $('.color-word', this).attr('data-canonicalname') + ',';
    });
    newWords = 'words=' + newWords;
    //Now the words have to be injected into the window.location:
    var url = $('div#saveLocation').attr('href');
    if(url.search('words=') > 0) // We add words if we already have some.
      url = url.replace('words=', newWords);
    else // We add the whole variable if no words are selected.
      url += '&' + newWords.substring(0, newWords.length - 1);
    //Changing to the new page:
    window.location = url;
  });
  //Triggering filters initial:
  if($('#SpellingFilter').val() != '')
    spellingFilter();
  if($('#PhoneticFilter').val() != '')
    phoneticFilter();
  updateCount();
}
