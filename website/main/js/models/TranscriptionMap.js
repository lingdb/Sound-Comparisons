/***/
TranscriptionMap = Backbone.Model.extend({
  /**
    The update method is connected by the App,
    to listen on change:study of the window.App.dataStorage.
  */
  update: function(){
    var ds   = window.App.dataStorage
      , data = ds.get('study');
    if(data && 'transcriptions' in data){
      console.log('TranscriptionMap.update()');
      var map = {}; // CONCAT(LanguageIx,IxElicitation,IxMorphologicalInstance) -> Transcription
      _.each(data.transcriptions, function(t, k){
        map[k] = new Transcription(t);
      }, this);
      this.set(map);
    }
  }
});
