/***/
WordCollection = Choice.extend({
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
    if(App.pageState.wordOrderIsAlphabetical()){ // Alphabetical order
      var ma = a.getModernName(), mb = b.getModernName();
      if(ma > mb) return  1;
      if(ma < mb) return -1;
    }else{ // Logical order
      var ma = a.getMeaningGroup(), mb = b.getMeaningGroup();
      if(ma && mb){
        var maId = ma.getId(), mbId = mb.getId();
        if(maId > mbId) return  1;
        if(maId < mbId) return -1;
        var aId = a.getId(), bId = b.getId();
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
});
