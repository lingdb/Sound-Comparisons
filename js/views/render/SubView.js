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
    /**
      @param siteLanguage String
      @param study String
      @return prom Deferred
      Attempts to load the given siteLanguage and study.
      If study fails, SubView.noStudy will be executed.
      if siteLanguage fails, console.log will be used.
      Returns Deferred to allow for further handling.
    */
  , loadBasic: function(siteLanguage, study){
      var prom = $.Deferred(), t = this;
      App.translationStorage.setTranslation(siteLanguage).fail(function(err){
        console.log('Error setting siteLanguage in SubView.loadBasic('+siteLanguage+',…)\n'+err);
      }).always(function(){
        App.study.setStudy(study).fail(function(){
          t.noStudy(study);
          prom.reject();
        }).done(function(){
          prom.resolve();
        });
      });
      return prom.promise();
    }
  });
});
