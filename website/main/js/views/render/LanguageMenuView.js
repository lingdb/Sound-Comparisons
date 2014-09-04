/**
  The LanguageMenuView will be used by the Renderer.
  It will set it's own model and handle it similar to TopMenuView.
*/
LanguageMenuView = Backbone.View.extend({
  initialize: function(){
    //Setting callbacks to update model:
    App.translationStorage.on('change:translationId', function(){
      this.updateStatic();
    }, this);
    //FIXME add missing callbacks.
    //Initial model:
    this.model = {};
  }
  /***/
, updateStatic: function(){
    var staticT = App.translationStorage.translateStatic({
      headline:      'menu_regions_headline'
    , languageSets:  'menu_regions_languageSets_title'
    , collapseTitle: 'menu_regions_languageSets_collapse'
    , expandTitle:   'menu_regions_languageSets_expand'
    });
    staticT.languageSets += ':';
    this.setModel(staticT);
  }
, render: function(){
    console.log('LanguageMenuView.render()');
    this.$el.html(App.templateStorage.render('LanguageMenu', {LanguageMenu: this.model}));
  }
  /**
    Basically the same as TopMenuView:setModel,
    this overwrites the current model with the given one performing a deep merge.
  */
, setModel: function(m){
    this.model = $.extend(true, this.model, m);
  }
});
