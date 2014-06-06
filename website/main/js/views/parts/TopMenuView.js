/*
  el:    TopMenu
  model: TemplateStorage
*/
TopMenuView = PartView.extend({
  initialize: function(){
    this.part = 'TopMenu';
    PartView.prototype.initialize.apply(this, arguments);
  }
, replaceEl: function(){
    this.$el = $('#topMenu');
    this.el  = this.$el.get(0);
  }
});
