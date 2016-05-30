"use strict";
define(['collections/WordCollection','underscore'], function(WordCollection,_){
  /**
    The FilteredWordCollection spots an instance of WordCollection,
    and represents a subset of its models as its own collection.
    It will be used to implement the Search/Filter functionality from the WordMenu
    as a collection.
  */
  return WordCollection.extend({
    filterOptions: {
      /*
        usePhonetics shall be true, iff we'd like to filter words by their phonetic transcriptions
        rather than using the written form in the currently selected spelling language.
      */
      usePhonetics: false
      /**
        Regex is a String describing a regex that we will filter the wordCollection by.
      */
    , regex: ''
    }
   , initialize: function(){
      /*
        Binding on events of WordCollection instance:
        See https://stackoverflow.com/q/12279236/448591
        This is necessary for first rendering, and will probably come in handy when changing study…
      */
      App.wordCollection.on('change reset add remove', this.filterWords, this);
    }
    /**
      @param options Object following the schema of filterOptions.
      Updates the models of this collection by filtering the models of App.wordCollection.
    */
  , filterWords: function(options){
      //Sanitizing options parameter:
      options = _.isObject(options) ? options : {};
      //Updating filterOptions:
      _.extend(this.filterOptions, options);
      //Using current options:
      options = this.filterOptions;
      //Unfiltered words:
      var toFilter = [];//[[FilterString, Word]], may contain duplicate Words
      if(options.usePhonetics !== true){
        var spLang = App.pageState.getSpLang();
        App.wordCollection.each(function(word){
          var text = word.getNameFor(spLang);
          if(_.isArray(text)){
            _.each(text, function(t){ toFilter.push([t, word]); }, this);
          }else{
            toFilter.push([text, word]);
          }
        }, this);
      }else{
        var phLang = App.pageState.getPhLang();
        App.wordCollection.each(function(word){
          var phonetics = ['*'+word.getProtoName()];
          if(phLang){
            var tr = word.getTranscription(phLang);
            if(tr){
              var ps = tr.get('Phonetic');
              if(_.isString(ps)){
                phonetics = [ps];
              }else if(_.isArray(ps)){
                phonetics = ps;
              }
            }
          }
          _.each(_.flatten(phonetics), function(p){
            if(_.isEmpty(p)){ return; }
            toFilter.push([p, word]);
          }, this);
        }, this);
      }
      //Filtering words:
      var wMap = {};//Mapping Word.getId() -> Word
      _.each(toFilter, function(pair){
        var text = pair[0].toLowerCase()
          , word = pair[1];
        if(text.search(options.regex) >= 0){
          wMap[word.getId()] = word;
        }
      }, this);
      //Setting models:
      this.reset(_.values(wMap));
    }
    /**
      Reset the collection to include all models.
    */
  , clearFilter: function(){
      this.reset(App.wordCollection.models);
    }
    /**
      @overrides collections/Selection.getSelected(pvk)
      @param [pvk] String
      @return selected [Word]
      Returns the selected Words as an array,
      but only those that survive the filter,
      because the final collection for them is not known.
      If pvk is given, it is used as the pageView to retrieve the selection for.
      Otherwise the current pageView will be used.
    */
  , getSelected: function(pvk){
      //FIXME IMPLEMENT!
    }
  });
});
