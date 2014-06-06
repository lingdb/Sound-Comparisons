/*
  el:    TopMenu
  model: TemplateStorage
*/
WordMenuView = PartView.extend({
  initialize: function(){
    this.part = 'WordMenu';
    PartView.prototype.initialize.apply(this, arguments);
  }
, replaceEl: function(){
    this.$el = $('#rightMenu');
    this.el  = this.$el.get(0);
  }
});
