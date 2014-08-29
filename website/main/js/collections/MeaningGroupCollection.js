/***/
MeaningGroupCollection = Backbone.Collection.extend({
  model: MeaningGroup
  /**
    Custom comparator to make sure MeaningGroups are sorted by MeaningGroupIx
  */
, comparator: function(a, b){
    var x = a.get('MeaningGroupIx')
      , y = b.get('MeaningGroupIx')
    if(x > y) return -1;
    if(x < y) return  1;
    return 0;
  }
  /**
    The update method is connected by the App,
    to listen on change:global of the App.dataStorage.
  */
, update: function(){ 
    var ds   = App.dataStorage
      , data = ds.get('global').global;
    if(data && 'meaningGroups' in data){
      console.log('MeaningGroupCollection.update()');
      this.reset(data.meaningGroups);
    }
  }
});
