"use strict";
/**
  This model handles the options associated with the download options given in the TopMenu.
  It is important to track these options, because they're purely client side settings.
*/
var DownloadOptions = Backbone.Model.extend({
  defaults: {
    wordByWord: false
  , format:     'mp3'
  }
, initialize: function(){
    //Reading from storage:
    this.load();
    //Storing on change:
    this.on('change', this.store, this);
  }
, load: function(){
    var options = {};
    _.each(_.keys(this.defaults), function(k){
      var l = this.storageKey(k);
      if(l in localStorage){
        options[k] = localStorage[l];
      }
    }, this);
    this.set(options);
  }
, store: function(){
    _.each(_.keys(this.defaults), function(k){
      var l = this.storageKey(k), v = this.get(k);
      localStorage[l] = v;
    }, this);
  }
, storageKey: function(k){return "DownloadOptions_"+k;}
});
