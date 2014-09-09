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
});
