FamilyCollection = Backbone.Collection.extend({
  model: Family
  /**
    The update method is connected by the App,
    to listen on change:study of the window.App.dataStorage.
  */
, update: function(){
    var ds   = window.App.dataStorage
      , data = ds.get('study');
    if(data && 'families' in data){
      console.log('FamilyCollection.update()');
      this.reset(data.families);
    }
  }
});
