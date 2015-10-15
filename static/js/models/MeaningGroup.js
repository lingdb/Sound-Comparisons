"use strict";
define(['backbone','collections/WordCollection'], function(Backbone, WordCollection){
  /***/
  return Backbone.Model.extend({
    initialize: function(){
      //Field for memoization of the Words that belong to this MeaningGroup.
      this._words = null;
    }
    /**
      Necessary so that MeaningGroupCollection can extend Selection.
    */
  , getId: function(){
      return this.get('MeaningGroupIx');
    }
    /***/
  , getKey: function(){return this.get('Name');}
    /**
      Returns the name of a MeaningGroup in the current translation.
    */
  , getName: function(){
      var category = 'MeaningGroupsTranslationProvider'
        , field    = this.getId()
        , fallback = this.get('Name');
      return App.translationStorage.translateDynamic(category, field, fallback);
    }
    /**
      Returns a collection of words connected to this meaningGroup.
      Note, that the MeaningGroup cannot take advantage of memoization,
      as it isn't exchanged when switching studies.
    */
  , getWords: function(){
      if(this._words === null){
        this._words = this.filterWordCollection(App.wordCollection);
      }
      return this._words;
    }
    /**
      Works similar to getWords,
      but uses words from the FilteredWordCollection instead
      of App.wordCollection.
      Doesn't provide memoization because filter may change.
    */
  , getFilteredWords: function(){
      return this.filterWordCollection(App.filteredWordCollection);
    }
    /**
      @param collection WordCollection
      @return filtered WordCollection
      Returns a WordCollection that contains only Words that belong to this MeaningGroup.
    */
  , filterWordCollection: function(collection){
      var mId = this.getId(), ws = [];
      collection.each(function(w){
        var m = w.getMeaningGroup();
        if(m && m.getId() == mId) ws.push(w);
      });
      return new WordCollection(ws);
    }
  });
});
