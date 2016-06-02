"use strict";
define(['backbone','models/Word'], function(Backbone, Word){
  /***/
  return Backbone.Model.extend({
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
  , getWords: function(o){
      //Sanitize options:
      o = o || {'pageViewKey': ''};
      if(o.pageViewKey === ''){
        o.pageViewKey = App.pageState.getPageViewKey();
      }
      //Get defaults in case of MultiView:
      if(_.contains(['languagesXwords','wordsXlanguages'], o.pageViewKey)){
        var wIds = {} // WordId -> Boolean
          , xs = (o.pageViewKey === 'wordsXlanguages')
               ? this.get('words_WdsXLgs')
               : this.get('words_LgsXWds');
        _.each(xs, function(x){
          wIds[parseInt(x.IxElicitation, 10)] = true;
        }, this);
        //Search words:
        return App.wordCollection.filter(function(w){
          var ixE = parseInt(w.get('IxElicitation'), 10);
          return ixE in wIds;
        }, this);
      }
      //Default to first 5 in other cases:
      return _.take(App.wordCollection.models, 5);
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
      Returns the default Languages as array for the current Study and pageViewKey.
    */
  , getLanguages: function(o){
      //Sanitize options:
      o = o || {'pageViewKey': ''};
      if(o.pageViewKey === ''){
        o.pageViewKey = App.pageState.getPageViewKey();
      }
      //Get defaults in case of MultiView:
      if(_.contains(['languagesXwords','wordsXlanguages'], o.pageViewKey)){
        var lIds = {} // LanguageIx -> Boolean
          , xs = (o.pageViewKey === 'wordsXlanguages')
               ? this.get('languages_WdsXLgs')
               : this.get('languages_LgsXWds');
        _.each(xs, function(l){
          lIds[l.LanguageIx] = true;
        }, this);
        return App.languageCollection.filter(function(l){
          return l.get('LanguageIx') in lIds;
        }, this);
      }
      //Default to all in other cases:
      return App.languageCollection.models;
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
});
