"use strict";
define(['backbone','bootstrap'], function(Backbone){
  /**
    el: #loadModal
    model: undefined
  */
  return Backbone.View.extend({
    initialize: function(){
      this.$el.modal({backdrop: true, keyboard: false, show: false});
    }
  , loadStudy: function(promise){
      //Showing the modal:
      var data = App.translationStorage.translateStatic({
        headline: 'LoadModalView_studyHeadline'
      , description: 'LoadModalView_studyText'
      });
      data.exit = true;
      this.render(data);
      //Making sure we hide the modal again:
      var view = this;
      promise.always(function(){view.render();});
    }
  , noMap: function(){  // FIXME MAYBE REMOVE THIS?!
      var data = App.translationStorage.translateStatic({
        headline: 'LoadModalView_mapHeadline'
      , description: 'LoadModalView_mapText'
      });
      data.exit = true;
      this.render(data);
    }
  , render: function(model){
      if(!_.isObject(model)){
        this.$el.modal('hide');
        return false;
      }else{
        this.$el.html(App.templateStorage.render('LoadModal', model));
        this.$el.modal('show');
        return true;
      }
    }
  });
});
