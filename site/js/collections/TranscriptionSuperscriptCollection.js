"use strict";
/* global App */
/* eslint-disable no-console */
define(['underscore','backbone','models/TranscriptionSuperscript'], function(_, Backbone, TranscriptionSuperscript){
  /***/
  return Backbone.Collection.extend({
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
      var e, abbr, hvt;
      if(_.isString(field)){
        if(field in (this.transcriptionFieldLookup || {})){
          return this.getTranscriptionSuperscript(this.transcriptionFieldLookup[field]);
        }else if(field.length === 3){
          e = this.findWhere({IsoCode: field});
          if(e) {
            abbr = e.get('Abbreviation');
            hvt = e.get('FullNameForHoverText');
            abbr = App.translationStorage.translateDynamic('TranscrSuperscriptLenderLgsTranslationProvider-TranscrSuperscriptLenderLgs-Trans_Abbreviation',field,abbr);
            hvt = App.translationStorage.translateDynamic('TranscrSuperscriptLenderLgsTranslationProvider-TranscrSuperscriptLenderLgs-Trans_FullNameForHoverText',field,hvt);
            return {Abbreviation: abbr, FullNameForHoverText: hvt};
          }
        }
      }else if(_.isNumber(field)){
        var predicate = function(e){return parseInt(e.get('Ix')) === field;};
        e = this.find(predicate);
        if(e) {
          abbr = e.get('Abbreviation');
          hvt = e.get('HoverText');
          abbr = App.translationStorage.translateDynamic('TranscrSuperscriptInfoTranslationProvider-TranscrSuperscriptInfo-Trans_Abbreviation',field,abbr);
          hvt = App.translationStorage.translateDynamic('TranscrSuperscriptInfoTranslationProvider-TranscrSuperscriptInfo-Trans_HoverText',field,hvt);
          return {Abbreviation: abbr, HoverText: hvt};
        }
      }
      console.log('Trans…Super…Collection.getTranscriptionSuperscript() had undefined field: '+field);
      return null;
    }
  });
});
