"use strict";
/**
  Note that contrary to the Transcription model implemented in php,
  this Transcription may have arrays of multiple values for some fields,
  instead of there being multiple Transcriptions that belong together,
  but repeat some fields while others change.
*/
var DummyTranscription = Transcription.extend({
  initialize: function(){
    this.set({Phonetic: 'missing'});
  }
, isDummy: function(){return true;}
});
