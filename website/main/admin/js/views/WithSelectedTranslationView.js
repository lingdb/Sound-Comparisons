/**
  Model is expected to be the current translation.
*/
WithSelectedTranslationView = Backbone.View.extend({
  initialize: function(){
    this.listenTo(this.model, 'change', this.render);
    this.basicInput  = window.Translation.views.basicInput;
    this.bInpMirror  = $('#BasicTranslationPageListMirror');
    this.searchInput = window.Translation.views.searchInput;
  }
, render: function(){
    if(this.model.get('TranslationId')){
      this.$el.show();
    }else{
      this.$el.hide();
    }
  }
, events: {
    'click #Translations_Translate':       'clickTranslate'
  , 'click #Translations_TranslateSearch': 'clickSearch'
  , 'click #Translations_Export':          'clickExport'
  }
, clickTranslate: function(){
    this.basicInput.show();
    this.bInpMirror.show();
    this.searchInput.hide();
  }
, clickSearch: function(){
    this.searchInput.show();
    this.basicInput.hide();
    this.bInpMirror.hide();
  }
, clickExport: function(){
    window.open('query/export.php', '_blank');
  }
});
