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
    App.translationStorage.on('change:translationId', function(){
      App.views.renderer.callUpdates(this);
    }, this);
    //Calling updates:
    App.views.renderer.callUpdates(this);
  }
, updateStatic: function(){
    var staticT = App.translationStorage.translateStatic({
      title: 'menu_words_words'
    });
    this.setModel(staticT);
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
