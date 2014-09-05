/***/
MeaningGroup = Backbone.Model.extend({
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
      var query = {MeaningGroupIx: this.getId()}
        , words = App.wordCollection.where(query);
      _.each(words, function(w){w._meaningGroup = this;}, this);
      this._words = new WordCollection(words);
    }
    return this._words;
  }
});
