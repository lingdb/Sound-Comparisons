"use strict";
define(['backbone','models/Transcription','models/DummyTranscription'], function(Backbone, Transcription, DummyTranscription){
  /***/
  return Backbone.Model.extend({
    /**
      The update method is connected by the App,
      to listen on change:study of the window.App.dataStorage.
    */
    update: function(){
      var ds   = window.App.dataStorage
        , data = ds.get('study');
      if(data && 'transcriptions' in data){
        console.log('TranscriptionMap.update()');
        var map = {}; // CONCAT(LanguageIx,IxElicitation,IxMorphologicalInstance) -> Transcription
        _.each(data.transcriptions, function(t, k){
          map[k] = new Transcription(t);
        }, this);
        this.set(map);
      }
    }
    /**
      Searches the transcription for a combination of language and word.
    */
  , getTranscription: function(l, w){
      var t = this.get(l.get('LanguageIx') + w.getId()) || new DummyTranscription();
      t.set({language: l, word: w});
      return t;
    }
    /***/
  , getDummies: function(){
      return _.filter(_.values(this.attributes), function(t){return t.isDummy();});
    }
  });
});
