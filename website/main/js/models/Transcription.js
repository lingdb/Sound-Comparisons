/**
  Note that contrary to the Transcription model implemented in php,
  this Transcription may have arrays of multiple values for some fields,
  instead of there being multiple Transcriptions that belong together,
  but repeat some fields while others change.
*/
Transcription = Backbone.Model.extend({
  defaults: {
    //Fields for the language and word a transcription belongs to.
    //These are set by TranscriptionMap:getTranscription.
    language: null
  , word:     null
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
      ret.push(App.transcriptionSuperscriptCollection.getTranscriptionSuperscript(key))
    }, isOne = function(key){
      return parseInt(fields[key]) === 1;
    }, runOne = function(key){
      if(isOne(key)){addKey(key);}
    }, notEmpty = function(key){
      return !(_.isEmpty(fields[key]));
    }, runEmpty = function(key){
      if(notEmpty(key)){
        ret.push(App.transcriptionSuperscriptCollection.getTranscriptionSuperscript(key)+key)
      }
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
      ret.push(App.transcriptionSuperscriptCollection.getTranscriptionSuperscript(fields[key]));
    }
    //Done:
    return ret;
  }
  /**
    Returns the Phonetics for a Transcription as an object.
    Uses getSuperscriptInfo.
  */
, getPhonetics: function(){
    //Note that both phonetics and sources will be sanitized for the first case.
    var phonetics = this.get('Phonetic')   // [String]   || String
      , sources   = this.get('soundPaths') // [[String]] || [String]
      , superScr  = this.getSuperscriptInfo()
      , ps        = [];
    //Sanitizing phonetics:
    if(_.isEmpty(phonetics))  phonetics = '--';
    if(!_.isArray(phonetics)) phonetics = [phonetics];
    //Sanitizing sources:
    if(!_.isArray(sources))    sources = [];
    if(sources.length === 0)   sources = [sources];
    if(_.isString(sources[0])) sources = [sources];
    //Iterating phonetics:
    for(var i = 0; i < phonetics.length; i++){
      var phonetic = phonetics[i]
        , source   = sources.shift() || [] //TODO filter ogg/mp3 depending on browser.
        , language = this.get('language')
        , word     = this.get('word')
        , p = { // Data gathered for phonetic:
          historical:  language.isHistorical()
        , fileMissing: source.length === 0
        , phonetic:    phonetic
        , srcs:        JSON.stringify(source)
        , _srcs:       source
        , hasTrans:    language.hasTranscriptions()
        , identifier:  { word:     word.getId()
                       , language: language.getId()
                       , study:    App.study.getId()
                       , n:        i }
        };
      //Not cognate:
      if(i < superScr.length){
        var s = superScr[i];
        if(s.length >= 2){
          p.notCognate = {
            sInf: s[0]
          , ttip: s[1]
          };
        }
      }
      //Subscript:
      if(phonetics.length > 1){
        var isLex = _.any(source, function(s){
          if(s.match(/_lex/)) return true;
          return false;
        }, this);
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
});
