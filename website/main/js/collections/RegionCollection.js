/***/
RegionCollection = Selection.extend({
  model: Region
  /**
    The update method is connected by the App,
    to listen on change:study of the window.App.dataStorage.
  */
, update: function(){
    var ds   = window.App.dataStorage
      , data = ds.get('study');
    if(data && 'regions' in data){
      console.log('RegionCollection.update()');
      this.reset(data.regions);
    }
  }
});
