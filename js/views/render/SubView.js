"use strict";
define(['backbone'], function(Backbone){
  /**
    To be extended by views that are used as models for the Renderer.
  */
  return Backbone.View.extend({
    getKey: function(){throw 'Renderer.SubView:getKey should be overwritten!';}
  , render: function(){throw 'Renderer.SubView:render should be overwritten!';}
  , isActive: function(){return App.pageState.isPageView(this);}
  /**
    Function to be called when loading a study failes.
    Default implementation will cause a translated alert
    explaining the situation.
    Afterwards the SubView may do what it likes.
  */
  , noStudy: function(study){
      var t = App.translationStorage, msg = t.translateStatic('failedFetchStudy');
      window.alert(t.placeInTranslation(msg, [study]));
    }
  });
});
