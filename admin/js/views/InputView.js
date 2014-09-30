/**
  model: any Collection
  el: any element that can be hidden
*/
InputView = Backbone.View.extend({
  show: function(){
    this.model.reset();
    this.$el.show();
  }
, hide: function(){
    this.$el.hide();
  }
});
