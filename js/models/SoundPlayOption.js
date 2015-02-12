/* global SoundPlayOption: true */
"use strict";
var SoundPlayOption = Backbone.Model.extend({
  initialize: function(){
    //Loading the playMode:
    var mode = App.storage.playMode;
    if(!mode) mode = 'hover';
    this.set({playMode: mode});
    //Making sure we save the playMode:
    this.on('change:playMode', this.saveMode, this);
  }
, saveMode: function(){
    App.storage.playMode = this.get('playMode');
  }
, playOnHover: function(){return this.get('playMode') === 'hover';}
, playOnClick: function(){return this.get('playMode') === 'click';}
});
