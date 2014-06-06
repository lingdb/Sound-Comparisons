/*
  el:    LanguageMenu
  model: TemplateStorage
*/
LanguageMenuView = PartView.extend({
  initialize: function(){
    this.part = 'LanguageMenu';
    PartView.prototype.initialize.apply(this, arguments);
  }
, replaceEl: function(){
    this.$el = $('#leftMenu');
    this.el  = this.$el.get(0);
  }
});
