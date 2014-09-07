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
      title: 'menu_words_words'
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
