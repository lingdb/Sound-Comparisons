/***/
Study = Backbone.Model.extend({
  defaults: {
  }
/**
  The update method is connected by the model,
  to listen on change:study of the window.App.dataStorage.
  Update also needs to be called once eventlistening is setup in App.js
*/
, update: function(){
    var ds   = window.App.dataStorage
      , data = ds.get('study');
    if(data && 'study' in data){
      console.log('Study.update()');
      this.set(data.study);
    }
  }
});
