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
});
