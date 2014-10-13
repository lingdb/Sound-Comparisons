"use strict";
/**
  el: #appSetup
  model: LoadingBar
*/
var SetupBarView = Backbone.View.extend({
  events: {
    'click .clearStorage': 'clearStorage'
  }
, initialize: function(){
    //Updating the bar state:
    this.model.on('change:loaded', this.render, this);
    //Finishing the setup:
    this.model.onFinish(this.finish, this);
  }
, render: function(){
    var segments = this.model.get('segments')
      , loaded   = this.model.get('loaded')
      , width    = (segments === 0) ? 0
                 : (loaded   === 0) ? 100
                 : Math.floor(loaded / segments * 100);
    this.$('.bar').css('width', width+'%');
  }
  /**
    Function to be called once the model finishes.
  */
, finish: function(){
    //Removing the SetupBarView from the App:
    delete window.App.views['setupBar'];
    //No longer listen to events from model:
    this.model.off(null, null, this);
    //Remove the el from the document:
    this.$el.remove();
    //Display hidelinks:
    $('.container-fluid').removeClass('hide');
    //Redraw of languageView if necessary:
    App.views.renderer.model.languageView.redraw();
    //Making sure this callback only works once:
    return false;
  }
, clearStorage: function(){
    App.storage.clear();
    location.reload(true);
  }
});
