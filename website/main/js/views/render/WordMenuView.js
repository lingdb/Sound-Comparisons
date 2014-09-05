/**
  The WordMenuView will be used by the Renderer.
  It will set it's own model and handle it similar to TopMenuView.
*/
WordMenuView = Backbone.View.extend({
  initialize: function(){
    //Setting the initial model:
    this.model = {};
  }
, activate: function(){
    //Setting callbacks to update model:
    App.translationStorage.on('change:translationId', this.updateStatic, this);
    App.pageState.on('change:wordOrder', this.updateSortBy, this);
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
      //FIXME WIP
      //, soundPath:        => Config::$soundPath
      }
    });
    this.setModel(staticT);
  }
, updateSortBy: function(){
    var data = {
      isLogical: App.pageState.wordOrderIsLogical()
    };
    data.link = data.isLogical ? 'href="#FIXME/implement setting word order to alphabetical"'
                               : 'href="#FIXME/implement setting word order to logical"';
    this.setModel({sortBy: data});
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
