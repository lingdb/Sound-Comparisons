/* global WordCollection: true */
"use strict";
/***/
var WordCollection = Choice.extend({
  model: Word
  /**
    The update method is connected by the App,
    to listen on change:study of the window.App.dataStorage.
  */
, update: function(){
    var ds   = window.App.dataStorage
      , data = ds.get('study');
    if(data && 'words' in data){
      console.log('WordCollection.update()');
      this.reset(data.words);
    }
  }
  /**
    Custom comparator that sorts words by either alphabetical order or by logical order.
  */
, comparator: function(a, b){
    var ma, mb;
    if(App.pageState.wordOrderIsAlphabetical()){ // Alphabetical order
      ma = a.getModernName().toLowerCase();
      mb = b.getModernName().toLowerCase();
      if(ma > mb) return  1;
      if(ma < mb) return -1;
    }else{ // Logical order
      ma = a.getMeaningGroup(); mb = b.getMeaningGroup();
      if(ma && mb){
        var maId = ma.getId(), mbId = mb.getId();
        if(maId > mbId) return  1;
        if(maId < mbId) return -1;
        var aId = a.getMgId(), bId = b.getMgId();
        if(aId > bId) return  1;
        if(aId < bId) return -1;
      }
    }
    //When in doubt, words are equal.
    return 0;
  }
  /**
    Called by App, to make sure wordCollection is sorted by the current WordOrder.
  */
, listenWordOrder: function(){
    App.pageState.on('change:wordOrder', this.sort, this);
  }
  /**
    Returns the default Words as array to be used as selection for the WordCollection.
  */
, getDefaultSelection: function(){
    var sel = App.defaults.getWords();
    if(sel.length === 0){
      return _.take(this.models, 5);
    }
    return sel;
  }
  /**
    Returns the default Word to be used as Choice for the WordCollection.
  */
, getDefaultChoice: function(){
    return App.defaults.getWord();
  }
  /**
    Overwriting Selection:getSelected to sort elements according to WordCollection:comparator.
  */
, getSelected: function(pvk){
    var selected = Selection.prototype.getSelected.call(this, pvk);
    selected.sort(this.comparator);
    return selected;
  }
});
