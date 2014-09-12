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
  /**
    Returns the default Languages as array to be used as selection for the LanguageCollection.
    Note that this method depends on the current PageView.
  */
, getDefaultSelection: function(){
    if(App.pageState.isMapView()){
      return App.defaults.getMapLanguages();
    }
    return App.defaults.getLanguages();
  }
  /**
    Returns the default Language to be used as Choice for the LanguageCollection.
  */
, getDefaultChoice: function(){
    return App.defaults.getLanguage();
  }
});
