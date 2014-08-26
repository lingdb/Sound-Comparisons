/***/
RegionLanguageCollection = Backbone.Collection.extend({
  model: RegionLanguage
  /**
    The update method is connected by the App,
    to listen on change:study of the window.App.dataStorage.
  */
, update: function(){
    var ds   = window.App.dataStorage
      , data = ds.get('study');
    if(data && 'regionLanguages' in data){
      console.log('RegionLanguageCollection.update()');
      this.reset(data.regionLanguages);
    }
  }
  /**
    Finds all Languages that belong to a given Region,
    via the n:m relationship given by the RegionLanguages.
  */
, findLanguages: function(region){
    var regionId = region.getId()
      , lSet     = {}; // LanguageIx -> Bool
    //Filling the lSet:
    this.each(function(rl){
      var rlId = rl.getRegionId();
      if(rlId === regionId){
        lSet[rl.get('LanguageIx')] = true;
      }
    });
    //Searching the languages:
    var langs = this.filter(function(l){
      var lIx = l.get('LanguageIx');
      return lIx in lSet;
    });
    return new LanguageCollection(langs);
  }
});
