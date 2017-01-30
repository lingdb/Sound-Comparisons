"use strict";
define(['models/Transcription'], function(Transcription){
  /**
    Note that contrary to the Transcription model implemented in php,
    this Transcription may have arrays of multiple values for some fields,
    instead of there being multiple Transcriptions that belong together,
    but repeat some fields while others change.
  */
  return Transcription.extend({
    initialize: function(){
      this.set({Phonetic: 'soon'});
    }
  , isDummy: function(){return true;}
  });
});
