"use strict";
/***/
var LanguageStatusTypeCollection = Backbone.Collection.extend({
  model: LanguageStatusType
  /**
    The update method is connected by the App,
    to listen on change:global of the window.App.dataStorage.
  */
, update: function(){
    var ds   = App.dataStorage
      , data = ds.get('global').global;
    if(data && 'languageStatusTypes' in data){
      console.log('LanguageStatusTypeCollection.update()');
      this.reset(data.languageStatusTypes);
    }
  }
});
