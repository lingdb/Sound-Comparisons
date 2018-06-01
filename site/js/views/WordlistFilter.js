"use strict";
/* global App */
define(['jquery', 'underscore', 'backbone'], function($, _, Backbone){
  /**
    Controls the filter box and mitigates it's effects.
  */
  return Backbone.View.extend({
    storage: {
      content:    'wordlistfilter_content'
    , inputId:    'wordlistfilter_inputId'
    , selectedId: 'wordlistfilter_selectedId'
    }
    /***/
  , initialize: function(){
      //If noFilter is set, reinitialize won't filter.
      this.noFilter = false;
      App.pageState.on('change:pageView', this.clearStorage, this);
      App.pageState.on('change:spLang', this.clearStorage, this);
      App.translationStorage.on('change:translationId', this.clearStorage, this);
    }
  /*
    This fullfills the works of initialize, but may also be called later on.
    The point is, that almost everything related to initialization may change,
    as parts of the page will be replaced on navigation.
  */
  , reinitialize: function(){
      if(window.App.studyWatcher.studyChanged()){
        this.clearStorage();
      }else{
        $(App.storage[this.storage.selectedId]).addClass('selected');
        $(App.storage[this.storage.inputId]).val(App.storage[this.storage.content] || '');
      }
      //Binding events:
      var t = this;
      $('#SpellingFilter').keyup(function(){ t.spellingFilter(); });
      $('#PhoneticFilter').keyup(function(){ t.phoneticFilter(); });
      $('#FilterAddMultiWords').click(function(){ t.pathfinder(true); });
      $('#FilterRefreshMultiWords').click(function(){ t.pathfinder(false); });
      $('#FilterClearMultiWords').click(function(){
        (function(r){
          var fragment = r.linkCurrent({config: r.getConfig(), words: []});
          r.navigate(fragment);
          App.study.trackLinks(fragment);
        })(App.router);
      });
      if(!this.noFilter){
        //Initial triggers:
        if($('#SpellingFilter').val() !== ''){
          this.spellingFilter();
        }
        if($('#PhoneticFilter').val() !== ''){
          this.phoneticFilter();
        }
        this.updateCount();
      }
    }
  , clearStorage: function(){
      _.each(_.values(this.storage), function(k){
        delete App.storage[k];
      });
      // Resetting the wordcollection:
      App.filteredWordCollection.clearFilter();
      return this;
    }
  , setStorage: function(selectedId, inputId, content){
      App.storage[this.storage.selectedId] = selectedId || '';
      App.storage[this.storage.inputId]    = inputId    || '';
      App.storage[this.storage.content]    = content    || '';
      return this;
    }
  , chkInput: function(input){
      if(input === ''){
        $('#FilterSpelling').removeClass('selected');
        $('#FilterPhonetic').removeClass('selected');
        this.clearStorage();
      }
      return this;
    }
    //Extends the input string so that it matches IPA Symbols.
  , enhanceIPA: function(input){
      if(input === null || _.isUndefined(input) || input === '')
        return input;
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
  //Projection from the LanguageTable to a filterSet
  , getLanguageTableSet: function(useTranscriptions){
      //If ¬useTranscriptions we use words to filter on.
      var set = $('#languageTable td.transcription').map(function(i, e){
        return { target: $(e)
               , text:   useTranscriptions ? $('div.transcription', e).text()
                                           : $('.color-word', e).text()
        };
      });
      return set;
    }
  //Updating the count of filtered words:
  , updateCount: function(){
      var c = App.filteredWordCollection.length;
      $('#FilterFoundMultiWords').text(c);
      if(c === 0){
        var i = $('#PhoneticFilter');
        if(i.val() === '')
          i = $('#SpellingFilter');
        if(i.val() === '')
          return;
        i.addClass('filterempty');
      }else{
        $('#PhoneticFilter, #SpellingFilter').removeClass('filterempty');
      }
      //Rerendering {Map,Word}View if either is active:
      if(App.pageState.isPageView(['m','w'])){
        if(App.pageState.isPageView('m')){
          var m = App.views.renderer.model.mapView;
          m.updateWordHeadline();
          m.render({renderMap: false});
        }else if(App.pageState.isPageView('w')){
          var w = App.views.renderer.model.wordView;
          w.updateWordHeadline();
          w.render();
        }
      }
      //Done:
      return this;
    }
  //The magic filter function:
  , filter: function(input, isPhonetic){
      this.noFilter = true;
      if(isPhonetic === true){
        input = this.enhanceIPA(input);
      }
      //General rewriting of input:
      if(_.isString(input)){
        input = input.replace(/^#/, '^');
        input = input.replace(/#$/, '$');
      }
      //Filtering words:
      App.filteredWordCollection.filterWords({
        usePhonetics: isPhonetic
      , regex: input
      });
      //Calling updates for views as necessary:
      if(App.templateStorage.get('ready')){
        App.views.renderer.model.wordMenuView.updateWordList().renderMgWords();
        if(App.pageState.isPageView('l')){
          App.views.renderer.model.languageView.updateLanguageTable().render();
        }
      }
      this.noFilter = false;
      return this.updateCount();
    }
  , spellingFilter: function(){
      $('#PhoneticFilter').val('');
      $('#FilterPhonetic').removeClass('selected');
      $('#FilterSpelling').addClass('selected');
      var v = $('#SpellingFilter').val();
      var input = (!_.isEmpty(v)) ? v.toLowerCase() : '';
      this.setStorage('#FilterSpelling', '#SpellingFilter', input);
      this.filter(input, false);
      return this.chkInput(input);
    }
  , phoneticFilter: function(){
      $('#SpellingFilter').val('');
      $('#FilterSpelling').removeClass('selected');
      $('#FilterPhonetic').addClass('selected');
      var input = $('#PhoneticFilter').val();
      this.setStorage('#FilterPhonetic', '#PhoneticFilter', input);
      this.filter(input, true);
      return this.chkInput(input);
    }
    //The function that leads the way:
  , pathfinder: function(keep){
      var words = keep ? App.wordCollection.getSelected() : []
        , wIds  = {};
      //Finding currently visible words:
      $('ul.wordList li:visible').each(function(){
        if($(this).parent().hasClass('selected'))
          return;
        var wId = $('.color-word', this).attr('data-wordId');
        wIds[wId] = true;
      });
      //Adding to words:
      App.wordCollection.each(function(w){
        if(w.getId() in wIds){
          words.push(w);
        }
      });
      //Updating fragment:
      (function(r){
        var fragment = r.linkCurrent({config: r.getConfig(), words: words});
        r.navigate(fragment);
        App.study.trackLinks(fragment);
      })(App.router);
    }
  });
});
