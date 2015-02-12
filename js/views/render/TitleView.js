/* global TitleView: true */
"use strict";
/**
  model: TranslationStorage
  el: head>title
*/
var TitleView = Backbone.View.extend({
  render: function(){
    var x = this.model.translateStatic('website_title_prefix')
      , y = App.study.getTitle() || App.study.getId()
      , z = this.model.translateStatic('website_title_suffix');
    this.$el.text(x+' '+y+' '+z);
  }
});
