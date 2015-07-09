"use strict";
define(['backbone'], function(Backbone){
  /**
    model: TranslationStorage
    el: head>title
  */
  return Backbone.View.extend({
    render: function(){
      var x = this.model.translateStatic('website_title_prefix')
        , y = App.study.getTitle() || App.study.getId()
        , z = this.model.translateStatic('website_title_suffix');
      this.$el.text(y+' - '+x+' - '+z);
    }
  });
});
