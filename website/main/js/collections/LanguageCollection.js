/***/
LanguageCollection = Choice.extend({
  model: Language
  /**
    LanguageCollection get's its own initialize so that it can handle language keys on reset.
    The problem here is, that the ShortName of a language may not always be enough to identify it.
    For the language to decide if the ShortName is enough, it needs to know if another language has the same key.
    Therefore we build a map of ShortNames to counts, that will be accessible for languages.
    To support this, the LanguageCollection offers a 'shortNameCount' method.
  */
, initialize: function(){
    this.shortNameMap = null;
    this.on('reset', this.countShortNames, this);
    //Calling superconstrucor:
    Choice.prototype.initialize.apply(this, arguments);
  }
  /**
    Builds the shortNameMap for this LanguageCollection.
  */
, mapShortNames: function(){
    this.shortNameMap = {};
    this.each(function(l){
      var sn = l.get('ShortName');
      if(sn in this.shortNameMap){
        this.shortNameMap[sn] += 1;
      }else{
        this.shortNameMap[sn] = 1;
      }
    }, this);
    console.log('Created shortNameMap!');
    console.log(this.shortNameMap);
  }
  /**
    The shortNameCount described from initialize, to aid Language:getKey
  */
, shortNameCount: function(name){
    if(this.shortNameMap === null){
      this.mapShortNames();
    }
    return this.shortNameMap[name] || 0;
  }
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
