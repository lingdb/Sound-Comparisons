/***/
LanguageCollection = Choice.extend({
  model: Language
  /**
    The update method is connected by the App,
    to listen on change:study of the window.App.dataStorage.
  */
, update: function(){
    var ds   = window.App.dataStorage
      , data = ds.get('study');
    if(data && 'languages' in data){
      console.log('LanguageCollection.update()');
      if('_spellingLanguages' in this){
        delete this['_spellingLanguages'];
      }
      this.reset(data.languages);
    }
  }
  /***/
, getDefaultPhoneticLanguage: function(){
    return this.find(function(l){
      return l.isDefaultPhoneticLanguage() || false;
    });
  }
  /***/
, getSpellingLanguages: function(){
    if(!this._spellingLanguages){
      var langs = this.filter(function(l){
        return parseInt(l.get('IsSpellingRfcLang')) === 1;
      }, this);
      this._spellingLanguages = new LanguageCollection(langs);
    }
    return this._spellingLanguages;
  }
});
