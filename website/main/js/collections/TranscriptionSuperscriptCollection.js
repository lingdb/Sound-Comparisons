/***/
TranscriptionSuperscriptCollection = Backbone.Collection.extend({
  model: TranscriptionSuperscript
  /**
    The update method is connected by the App,
    to listen on change:global of the App.dataStorage.
  */
, update: function(){
    var ds   = App.dataStorage
      , data = ds.get('global').global;
    this.models = [];
    _.each(['transcrSuperscriptInfo','transcrSuperscriptLenderLgs'], function(k){
      if(k in data){
        this.add(data[k]);
      }
    }, this);
    console.log('TranscriptionSuperscriptCollection.update()');
  }
});
