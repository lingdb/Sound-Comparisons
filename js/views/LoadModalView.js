"use strict";
/**
  el: #loadModal
  model: undefined
*/
var LoadModalView = Backbone.View.extend({
  initialize: function(){
    $('#loadModal').modal({backdrop: true, keyboard: false, show: false});
    //this.$el.modal({backdrop: true, keyboard: false, show: false});
  }
, loadStudy: function(promise){
    //Showing the modal:
    var data = {
      exit: true
    , headline: 'Loading…'
    , description: 'The site is currently loading family data, this may take a moment.'
    };
    this.render(data);
    //Making sure we hide the modal again:
    var view = this;
    promise.always(function(){view.render();});
  }
, noMap: function(){
    this.render({
      exit: true
    , headline: 'Maps not accessable'
    , description: 'It appears that google maps could not be loaded. '
                  +'Please check your internet connection.'
    });
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
