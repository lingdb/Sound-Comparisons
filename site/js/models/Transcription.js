/* global App */
/* eslint-disable no-console */
"use strict";
define(['underscore','backbone'], function(_, Backbone){
  /**
    Note that contrary to the Transcription model implemented in php,
    this Transcription may have arrays of multiple values for some fields,
    instead of there being multiple Transcriptions that belong together,
    but repeat some fields while others change.
  */
  return Backbone.Model.extend({
    defaults: {
      //Fields for the language and word a transcription belongs to.
      //These are set by TranscriptionMap:getTranscription.
      language: null
    , word:     null
    }
    /**
      This was mainly build to enable export/soundfiles to identify transcriptions.
    */
  , getId: function(){
      var d = this.pick('language','word');
      //Fallback to generate Id from local data:
      if(d.language === null || d.word === null){
        var keys = ['LanguageIx','IxElicitation','IxMorphologicalInstance'];
        return _.map(keys, this.get, this).join('');
      }
      return [d.language.getId(), d.word.getId()].join('');
    }
    /**
      Returns the SuperscriptInfo for a Transcription as an object.
      A helper for getPhonetics.
    */
  , getSuperscriptInfo: function(){
      //The fields to judge:
      var fields = this.pick(
        'NotCognateWithMainWordInThisFamily'
      , 'CommonRootMorphemeStructDifferent'
      , 'DifferentMeaningToUsualForCognate'
      , 'ActualMeaningInThisLanguage'
      , 'OtherLexemeInLanguageForMeaning'
      , 'RootIsLoanWordFromKnownDonor'
      , 'RootSharedInAnotherFamily'
      , 'IsoCodeKnownDonor'
      ), ret = [];
      //Helper functions:
      var addKey = function(key){
        var x = App.transcriptionSuperscriptCollection.getTranscriptionSuperscript(key) || {};
        if('FullNameForHoverText' in x){
          x = [x.Abbreviation, x.FullNameForHoverText];
        }else if('HoverText' in x){
          x = [x.Abbreviation, x.HoverText];
        }else {x = [];}
        ret.push(x);
      }, isOne = function(key){
        return parseInt(fields[key]) === 1;
      }, runOne = function(key){
        if(isOne(key)){addKey(key);}
      }, notEmpty = function(key){
        return !(_.isEmpty(fields[key]));
      }, runEmpty = function(key){
        if(notEmpty(key)){addKey(key);}
      };
      //Putting helpers to use:
      _.each(['NotCognateWithMainWordInThisFamily'
             ,'CommonRootMorphemeStructDifferent'
             ,'DifferentMeaningToUsualForCognate']
             , runOne, this);
      _.each(['ActualMeaningInThisLanguage'
             ,'OtherLexemeInLanguageForMeaning']
             , runEmpty, this);
      _.each(['RootIsLoanWordFromKnownDonor'
             ,'RootSharedInAnotherFamily']
             , runOne, this);
      if(notEmpty('IsoCodeKnownDonor')){
        ret.push(App.transcriptionSuperscriptCollection.getTranscriptionSuperscript(fields.IsoCodeKnownDonor));
      }
      //Done:
      return ret;
    }
    /**
      @returns [[String]]
    */
  , getSoundfiles: function(){
      //We need to clone the soundPaths so that filtering and stuff can't do any harm.
      var sources = _.clone(this.get('soundPaths')); // [[String]] || [String]
      if(!_.isArray(sources))    sources = [];
      if(sources.length === 0)   sources = [sources];
      if(_.isString(sources[0])) sources = [sources];
      return sources;
    }
    /**
      Filters arrays of soundfiles for the selected wordByWordFormat.
      [String] -> [String]
    */
  , filterSoundfiles: function(xs){
      var suffix = App.pageState.get('wordByWordFormat');
      return _.filter(xs, function(x){
        // https://stackoverflow.com/questions/280634/endswith-in-javascript
        return x.indexOf(suffix, x.length - suffix.length) !== -1;
      });
    }
    /**
      We always produce something with getPhonetics,
      but sometimes it's something like 'play'|'soon'.
      hasPhonetics returns true, iff this is the case.
    */
  , hasPhonetics: function(){
      if(this.isDummy()) return false;
      var p = this.get('Phonetic');
      return !_.isEmpty(p);
    }
    /**
      Returns the Phonetics for a Transcription as an object.
      Uses getSuperscriptInfo.
    */
  , getPhonetics: function(){
      //Note that both phonetics and sources will be sanitized for the first case.
      var phonetics = this.get('Phonetic') // [String]   || String
        , sources   = this.getSoundfiles() // [[String]]
        , superScr  = this.getSuperscriptInfo()
        , ps        = [];
      //Sanitizing phonetics:
      if(window.App.storage.ShowDataAs === 'labels') {
        if(_.isEmpty(phonetics))  phonetics = '<i class="icon-play"></i>';
      }
      if(!_.isArray(phonetics)) phonetics = [phonetics];
      //WordByWord logic:
      var wordByWord = App.pageState.get('wordByWord');
      /*
        isLexPredicate used in loop below.
        Put here to not create it in a loop.
      */
      var isLexPredicate = function(s){
        if(_.isString(s)){
          if(s.match(/_lex/)) return true;
        }else{
          console.log('Strange source: '+typeof(s));
          console.log(s);
        }
        return false;
      };
      //Iterating phonetics:
      for(var i = 0; i < phonetics.length; i++){
        var phonetic = phonetics[i]//String
          , source   = sources.shift() || []//[String]
          , language = this.get('language')
          , word     = this.get('word')
          , p = { // Data gathered for phonetic:
              historical:  language.isHistorical()
            , fileMissing: source.length === 0
            , smallCaps:   phonetic === 'play'
            , phonetic:    phonetic
            , srcs:        JSON.stringify(source)
            , _srcs:       this.filterSoundfiles(source)
            , hasTrans:    language.hasTranscriptions()
            , identifier:  { word:     word.getId()
                           , language: language.getId()
                           , study:    App.study.getId()
                           , n:        i }
            , wordByWord:  wordByWord
          };
        //Guarding for #351:
        if(_.some(['--','..','...','…'], function(s){return p.phonetic === s;})){
          continue;
        }
        //Not cognate:
        if(i < superScr.length){
          var s = superScr[i] || [];
          if(s.length >= 2){
            p.notCognate = {
              sInf: s[0]
            , ttip: s[1]
            };
          }
        }
        //Subscript:
        if(phonetics.length > 1){
          var isLex = _.any(source, isLexPredicate, this);
          if(isLex){
            p.subscript = {
              ttip: App.translationStorage.translateStatic('tooltip_subscript_differentVariants')
            , subscript: i + 1
            };
          }
        }
        //Done:
        ps.push(p);
      }
      return ps;
    }
    /**
      Returns an array of all non empty SpellingAltv[12] fields in a transcription.
    */
  , getSpellingAltv: function(){
      //Notice that the add function also handles the case that one of the fields may be an array.
      var alt = [], add = function(a){
        if(_.isArray(a)){
          _.each(a, add);
        }else if(!_.isEmpty(a)){
          alt.push(a);
        }
      };
      add([this.get('SpellingAltv1'),this.get('SpellingAltv2')]);
      return alt;
    }
    /**
      Returns the alternative Spelling of the word, which belongs to the Transcription.
      If an altSpelling can't be found, but the Transcriptions Language is a RfcLanguage,
      the ModernName of the Word is returned.
      If the Language is not a RfcLanguage, but has one,
      getAltSpelling for the Transcription of that RfcLanguage and the same Word is returned.
      If all these approaches fail, null is returned.
    */
  , getAltSpelling: function(){
      var language = this.get('language'), word = this.get('word')
        , alts = this.getSpellingAltv();
      if(alts.length > 0){
        var proto  = language.isProto() ? '*' : ''
          , altSp  = proto + alts[0]
          , wTrans = word.getNameFor(App.pageState.getSpLang())
          , fail   = _.isArray(wTrans) ? _.any(wTrans, function(w){return w === altSp;}) : (wTrans === altSp);
        if(!fail) return altSp;
      }
      if(language.isRfcLanguage()) return word.getModernName();
      var rfc = language.getRfcLanguage();
      if(rfc){
        var t = App.transcriptionMap.getTranscription(rfc, word);
        return t.getAltSpelling();
      }
      return null;
    }
    /**
     returns cognate state
     1 := is not cognate; 0 := is cognate; -1 := undefined
    */
  , getCognateState: function(){
      if(this.isDummy()) return -1;
      var p = this.get('NotCognateWithMainWordInThisFamily');
      return parseInt(p);
    }
    /***/
  , isDummy: function(){
      var d = this.get('isDummy');
      return d || false;
    }
  });
});
