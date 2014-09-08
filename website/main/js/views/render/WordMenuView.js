/**
  The WordMenuView will be used by the Renderer.
  It will set it's own model and handle it similar to TopMenuView.
*/
WordMenuView = Backbone.View.extend({
  initialize: function(){
    //Setting the initial model:
    this.model = {
      searchFilter: {
        fClearAllLink: 'data-href="#FIXME/implement removing all words"'
      }
    };
  }
, activate: function(){
    //Setting callbacks to update model:
    App.translationStorage.on('change:translationId', function(){
      this.updateStatic();
      this.updateSearchFilter();
    }, this);
    App.pageState.on('change:wordOrder', this.updateSortBy, this);
    App.pageState.on('change:spLang', this.updateSearchFilter, this);
    App.pageState.on('change:phLang', this.updateSearchFilter, this);
    //Calling updates:
    App.views.renderer.callUpdates(this);
  }
, updateStatic: function(){
    var staticT = App.translationStorage.translateStatic({
      title:       'menu_words_words'
    , meaningSets: 'menu_words_meaningSets_title'
    , expand:      'menu_words_meaningSets_expand'
    , collapse:    'menu_words_meaningSets_collapse'
    , sortBy: {
        sortBy: 'menu_words_selectortext'
      , aOrder: 'menu_words_selector_rfclangs'
      , lOrder: 'menu_words_sortelicitation'
      }
    , searchFilter: {
        sfby:             'menu_words_filter_head'
      , spelling:         'menu_words_filterspelling'
      , phonetics:        'menu_words_filterphonetics'
      , soundFile:        'menu_words_filter_fonetics_file'
      , fonetics:         'menu_words_filter_fonetics'
      , psTarget:         'menu_words_filter_regex_link'
      , psHover:          'menu_words_filter_regex_hover'
      , in:               'menu_words_filter_spphin'
      , ipaOpenTitle:     'menu_words_open_ipaKeyboard'
      , filterFoundWords: 'menu_words_filterFoundWords'
      , fTitle:           'menu_words_filterTitleMultiWords'
      , fAddAll:          'menu_words_filterAddMultiWords'
      , fRefresh:         'menu_words_filterRefreshMultiWords'
      , fClearAll:        'menu_words_filterClearAllWords'
      }
    });
    this.setModel(staticT);
  }
, updateSoundPath: function(){
    this.setModel({
      searchFilter: {
        soundPath: App.dataStorage.get('global').global.soundPath
      }
    });
  }
, updateSortBy: function(){
    var data = {
      isLogical: App.pageState.wordOrderIsLogical()
    };
    data.link = data.isLogical ? 'href="#FIXME/implement setting word order to alphabetical"'
                               : 'href="#FIXME/implement setting word order to logical"';
    this.setModel({sortBy: data});
  }
  /**
    Since big parts of the searchFilter are already done by updateStatic,
    this method has a focus on building the {sp,ph}List entries.
  */
, updateSearchFilter: function(){
    var data = {spList: {}, phList: {}};
    //Filling spList:
    var spLang = App.pageState.getSpLang();
    data.spList.current = spLang ? spLang.getSpellingName() : App.translationStorage.getName();
    //First item:
    if(spLang){
      data.spList.options = [{
        link: 'data-href="#FIXME/implement setting the spLang"'
      , name: App.translationStorage.getName()
      }];
    }
    //Other items:
    var spId = spLang ? spLang.getId() : -1;
    App.languageCollection.getSpellingLanguages().each(function(l){
      if(l.getId() === spId) return;
      data.spList.options.push({
        link: 'data-href="#FIXME/implement setting the spLang"'
      , name: l.getSpellingName()
      });
    }, this);
    //Filling phList:
    var phLang = App.pageState.getPhLang()
      , phId   = phLang.getId();
    //Initial data for phList
    data.phList.current = phLang.getShortName();
    data.phList.options = [];
    //Other phLangs:
    App.languageCollection.each(function(l){
      if(l.getId() === phId) return;
      data.phList.options.push({
        link: 'data-href="#FIXME/implement setting the phLang"'
      , href: l.getShortName()
      });
    }, this);
    //Use App.pageState.get{Sp,Ph}Lang
    this.setModel({searchFilter: data});
  }
  /**
    FIXME figure out the necessary callbacks, and implement them.
    These should include: translations, wordCollection, meaningGroupCollection, wordOrder.
  */
, updateWordList: function(){
    var data = {
      isLogical: App.pageState.wordOrderIsLogical()
    };
    if(data.isLogical){
      data.ahref = 'href="#FIXME/implement adding all meaningGroups"';
      data.nhref = 'href="#FIXME/implement removing all meaningGroups"';
      data.meaningGroups = [];
      var isMulti = App.pageState.isMultiView();
      App.meaningGroupsCollection.each(function(m){
        var collapsed = !App.meaningGroupCollection.isSelected(m)
          , mg = {
              name: m.getName()
            , fold: collapsed ? 'mgFold' : 'mgUnfold'
            , triangle: collapsed ? 'icon-chevron-up rotate90' : 'icon-chevron-down'
            , link: 'href="#FIXME/implement toggeling of meaningGroups"'
            }
          , words = m.getWords();
        if(isMulti){
          var box = {icon: 'icon-chkbox-custom'};
          switch(App.wordCollection.areSelected(words)){
            case 'all':
              box.icon = 'icon-check';
              box.link = 'data-href="#FIXME/implement removing multiple words"';
              box.ttip = App.translationStorage.translateStatic('multimenu_tooltip_minus');
            break;
            case 'some':
              box.icon = 'icon-chkbox-half-custom';
            case 'none':
              box.link = 'data-href="#FIXME/implement adding multiple words"';
              box.ttip = App.translationStorage.translateStatic('multimenu_tooltip_plus');
          }
          mg.checkbox = box;
        }
        mg.wordList = this.buildWordList(words);
        data.meaningGroups.push(mg);
      }, this);
    }else{
      data.wordList = this.buildWordList(App.wordCollection);
    }
    this.setModel(data);
  }
  /**
    This is a helper method for updateWordList.
    It creates the wordList for a given Collection of Words,
    so that they can be embedded in meaningGroups or in the WordMenu directly.
  */
, buildWordList: function(words){
    var ws = [], isMulti = App.pageState.isMultiView();
    words.each(function(word){
      var w = {
        cname:    word.getKey()
      , selected: App.wordCollection.isSelected(word)
      , trans:    word.getModernName()
      , ttip:     word.getLongName()
      };
      //The checkbox/icon:
      if(isMulti){
        if(w.selected){
          w.icon = {
            ttip: App.translationStorage.translateStatic('multimenu_tooltip_del')
          , link: 'href="#FIXME/implement removing a word"'
          , icon: 'icon-check'
          };
        }else{
          w.icon = {
            ttip: App.translationStorage.translateStatic('multimenu_tooltip_add')
          , link: 'href="#FIXME/implement adding a word"'
          , icon: 'icon-chkbox-custom'
          };
        }
      }
      //Link for each word:
      w.link = App.pageState.isMapView()
             ? 'href="#FIXME/implement setting a word to mapView"'
             : 'href="#FIXME/implement wordview of a single word"';
      //Phonetics:
      var phonetics = '*'+word.getProtoName();
      if(phLang = App.pageState.getPhLang()){
        var tr = word.getTranscription(phLang);
        phonetics = tr.getPhonetics();
      }
      //Finish it:
      _.each(phonetics, function(p){
        ws.push($.extend(w, {phonetic: p}))
      }, this);
    }, this);
    return {words: ws};
  }
, render: function(){
    console.log('WordMenuView.render()');
    this.$el.html(App.templateStorage.render('WordMenu', {WordMenu: this.model}));
  }
  /**
    Basically the same as TopMenuView:setModel,
    this overwrites the current model with the given one performing a deep merge.
  */
, setModel: function(m){
    this.model = $.extend(true, this.model, m);
  }
});
