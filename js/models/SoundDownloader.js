/* global SoundDownloader: true */
"use strict";
/**
  The SoundDownloader aids downloading a .zip of all soundfiles currently in the content area.
*/
var SoundDownloader = Backbone.Model.extend({
  /**
    Gathers an array of all paths of currently displayed soundfiles
  */
  getTranscriptions: function(){
    var ps = App.pageState, ls = [], ws = [];
    if(_.any(['m','w'], ps.isPageView, ps)){
      //Transcriptions for all languages with the current word
      ws = [App.wordCollection.getChoice()];
      ls = App.languageCollection.models;
    }else if(ps.isPageView('l')){
      //Transcriptions for all words in the current language
      ls = [App.languageCollection.getChoice()];
      ws = App.wordCollection.models;
    }else if(ps.isMultiView()){
      //Transcriptions for all selected words in all selected languages
      ws = App.wordCollection.getSelected();
      ls = App.languageCollection.getSelected();
    }else{
      console.log('Unexpected case in SoundDownloader:getPaths!');
    }
    //Collecting transcriptions:
    var ts = [];
    _.each(ls, function(l){
      _.each(ws, function(w){
        var t = App.transcriptionMap.getTranscription(l, w);
        if(t) ts.push(t);
      }, this);
    }, this);
    return ts;
  }
  /***/
, mkPathDesc: function(){
    return _.map(this.getTranscriptions(), function(t){
      return t.getId();
    }).join(';');
  }
});
