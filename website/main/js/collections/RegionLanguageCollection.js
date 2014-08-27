/***/
RegionLanguageCollection = Backbone.Collection.extend({
  model: RegionLanguage
  /***/
, comparator: function(a, b){
    var as = a.sortValues()
      , bs = b.sortValues();
    if(as[0] > bs[0]) return -1;
    if(as[0] < bs[0]) return  1;
    if(as[1] > bs[1]) return -1;
    if(as[1] < bs[1]) return  1;
    return 0;
  }
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
    var langs = App.languageCollection.filter(function(l){
      var lIx = l.get('LanguageIx');
      return lIx in lSet;
    });
    return new LanguageCollection(langs);
  }
  /**
    Finds all Regions that belong to a given Language,
    via the n:m relationship given by the RegionLanguages.
  */
, findRegions: function(language){
    var languageId = language.get('LanguageIx')
      , rSet       = {}; // RegionId -> Bool
    //Filling the rSet:
    this.each(function(rl){
      var lId = rl.get('LanguageIx');
      if(lId === languageId){
        rSet[rl.getRegionId()] = true;
      }
    });
    //Searching the regions:
    var regions = App.regionCollection.filter(function(r){
      var rId = r.getId();
      return rId in rSet;
    });
    return new RegionCollection(regions);
  }
});
