/***/
MeaningGroup = Backbone.Model.extend({
  getName: function(){
    var category = 'MeaningGroupsTranslationProvider'
      , field    = this.get('MeaningGroupIx')
      , fallback = this.get('Name');
    return App.translationStorage.translateDynamic(category, field, fallback);
  }
  /**
    Returns a collection of words connected to this meaningGroup.
    Note, that the MeaningGroup cannot take advantage of memoization,
    as it isn't exchanged when switching studies.
  */
, getWords: function(){
    var query = {MeaningGroupIx: this.get('MeaningGroupIx')}
      , words = App.wordCollection.where(query);
    return new WordCollection(words);
  }
});
