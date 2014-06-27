/*
  el: head
  model: TemplateStorage
*/
HeadView = PartView.extend({
  initialize: function(){
    this.part = 'head';
    PartView.prototype.initialize.apply(this, arguments);
  }
, render: function(data){
    this.$('title').html(data.title);
  }
, replaceEl: function(){}
});
