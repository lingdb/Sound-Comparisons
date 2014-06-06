/*
  el:    TopMenu
  model: TemplateStorage
*/
ContentView = PartView.extend({
  initialize: function(){
    this.part = 'content';
    PartView.prototype.initialize.apply(this, arguments);
  }
, replaceEl: function(){
    this.$el = $('#contentArea');
    this.el  = this.$el.get(0);
  }
});
