"use strict";
/***/
var Defaults = Backbone.Model.extend({
  /**
    The update method is connected by the App,
    to listen on change:study of the window.App.dataStorage.
  */
  update: function(){
    var ds   = window.App.dataStorage
      , data = ds.get('study');
    if(data && 'defaults' in data){
      console.log('Defaults.update()');
      this.set(data.defaults);
    }
  }
  /**
    Returns the default Word for the current Study as defined in the v4.Default_Words table.
  */
, getWord: function(){
    var w   = new Word(this.get('word'))
      , wId = w.getId();
    //Searching word by wId:
    w = App.wordCollection.find(function(x){
      return x.getId() === wId;
    }, this);
    if(!w){//Fallback to first word:
      w = App.wordCollection.models[0];
    }
    return w;
  }
  /**
    Returns the default Words as an array for the current Study as defined in the v4.Default_Multiple_Words table.
  */
, getWords: function(){
    var wIds = {}; // WordId -> Boolean
    _.each(this.get('words'), function(w){
      var word = new Word(w);
      wIds[word.getId()] = true;
    }, this);
    //Search words:
    return App.wordCollection.filter(function(w){
      return w.getId() in wIds;
    }, this);
  }
  /**
    Returns the default Language for the current Study as defined in the v4.Default_Languages table.
  */
, getLanguage: function(){
    var query = this.get('language');
    //Try to find the language, fallback to first language:
    var l = App.languageCollection.findWhere(query);
    return l || App.languageCollection.models[0];
  }
  /**
    Returns the default Languages as array for the current Study
    as defined in the v4.Default_Multiple_Languages table.
  */
, getLanguages: function(){
    var lIds = {}; // LanguageIx -> Boolean
    _.each(this.get('languages'), function(l){
      lIds[l.LanguageIx] = true;
    }, this);
    return App.languageCollection.filter(function(l){
      return l.get('LanguageIx') in lIds;
    }, this);
  }
  /**
    Returns the default Languages as array for map use in the current Study
    as defined in the v4.Default_Languages_Exclude_Map table.
  */
, getMapLanguages: function(){
    var excludes = {}; // LanguageIx -> Boolean
    _.each(this.get('excludeMap'), function(l){
      excludes[l.LanguageIx] = true;
    }, this);
    return App.languageCollection.filter(function(l){
      return !(l.get('LanguageIx') in excludes);
    }, this);
  }
});
