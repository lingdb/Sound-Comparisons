/* global MeaningGroupCollection: true */
"use strict";
/***/
var MeaningGroupCollection = Selection.extend({
  model: MeaningGroup
  /**
    Custom comparator to make sure MeaningGroups are sorted by MeaningGroupIx
  */
, comparator: function(a, b){
    var x = parseInt(a.getId(), 10)
      , y = parseInt(b.getId(), 10);
    if(x > y) return  1;
    if(x < y) return -1;
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
  /**
    @param words [Word]
    Returns an object {mMap :: MgId -> MeaningGroup, wMap :: MgId -> [Word]}
    This Method is basically a helper method for the MultiViews,
    and implements bucket sort, which runs in O(n), to sort Words by MeaningGroup.
  */
, getMeaningGroupBuckets: function(words){
    var mMap = {}, wMap = {};
    _.each(words, function(w){
      var mg = w.getMeaningGroup(), mId = mg.getId();
      if(!(mId in mMap)) mMap[mId] = mg;
      if(mId in wMap){
        wMap[mId].push(w);
      }else{
        wMap[mId] = [w];
      }
    }, this);
    return {mMap: mMap, wMap: wMap};
  }
});
