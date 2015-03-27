/* global MeaningGroup: true */
"use strict";
/***/
var MeaningGroup = Backbone.Model.extend({
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
      var mId = this.getId();
      this._words = new WordCollection(App.wordCollection.filter(function(w){
        return w.getMgId() == mId;
      }));
    }
    return this._words;
  }
});
