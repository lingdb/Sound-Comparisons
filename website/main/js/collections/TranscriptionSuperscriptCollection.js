/***/
TranscriptionSuperscriptCollection = Backbone.Collection.extend({
  model: TranscriptionSuperscript
  /**
    The update method is connected by the App,
    to listen on change:global of the App.dataStorage.
  */
, update: function(){
    var ds   = App.dataStorage
      , data = ds.get('global').global;
    this.models = [];
    _.each(['transcrSuperscriptInfo','transcrSuperscriptLenderLgs'], function(k){
      if(k in data){
        this.add(data[k]);
      }
    }, this);
    console.log('TranscriptionSuperscriptCollection.update()');
  }
, transcriptionFieldLookup: {
    NotCognateWithMainWordInThisFamily: 1
  , CommonRootMorphemeStructDifferent:  2
  , DifferentMeaningToUsualForCognate:  3
  , ActualMeaningInThisLanguage:       11
  , OtherLexemeInLanguageForMeaning:   12
  , RootIsLoanWordFromKnownDonor:      21
  , RootSharedInAnotherFamily:         22
  }
, getTranscriptionSuperscript: function(field){
    if(_.isString(field)){
      if(field in this.transcriptionFieldLookup){
        return this.getTranscriptionSuperscript(this.transcriptionFieldLookup[field]);
      }else if(field.length === 3){
        if(e = this.findWhere({ISOCode: field}))
          return e.pick('Abbreviation', 'FullNameForHoverText');
      }
    }else if(_.isNumber(field)){
      var predicate = function(e){return parseInt(e.get('Ix')) === field;};
      if(e = this.find(predicate))
        return e.pick('Abbreviation', 'HoverText');
    }
    console.log('Trans…Super…Collection.getTranscriptionSuperscript() had undefined field: '+field);
    return null;
  }
});
