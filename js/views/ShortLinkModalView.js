"use strict";
define(['backbone','bootstrap'], function(Backbone){
  /**
    el: #shortLinkModal
    model: undefined
  */
  return Backbone.View.extend({
    initialize: function(){
      this.$el.modal({backdrop: true, show: false});
    }
    /**
      @param model [{label: String || 'default', url: String}]
      The passed model shall be of the same structure as the resolved promise
      from models/DataStorage.addShortLink().
    */
  , render: function(model){
      //Sanity check:
      if(!_.isArray(model)){
        console.log('Unexpected model structure in ShortLinkModalView.render()!');
        return;
      }
      //Building content to render with:
      var tStorage = App.translationStorage;
      var content = {
        headline: tStorage.translateStatic('shortLinkModal_headline')
      , entries: _.map(model, function(m){
          if(m.label === 'default'){
            m.label = tStorage.translateStatic('shortLinkModal_default');
          }
          return m;
        }, this)
      };
      //Rendering:
      this.$el.html(App.templateStorage.render('ShortLinkModal', content));
      this.$el.modal('show');
    }
  });
});
