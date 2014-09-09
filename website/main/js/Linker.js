/**
  The Linker extends the Configurator, which in turn extends the Sanitizer.
  Building links makes use of both, the Sanitizer and the Configurator.
  This Class is extended by the Router,
  which is the central link building/route processing entity in our App.
*/
Linker = Configurator.extend({
  /**
    Produces a config with configSet, and builds a link with linkCurrent.
    This shortens the code at several other places.
  */
  linkConfig: function(calls){
    var options = {config: this.configSet(calls)};
    return this.linkCurrent(options);
  }
  /**
    Creates a link in the current view using the given options.
    This is mainly helpful for config related changes.
  */
, linkCurrent: function(options){
    var callMap = {
      map:             'linkMapView'
    , word:            'linkWordView'
    , language:        'linkLanguageView'
    , languagesXwords: 'linkLanguageWordView'
    , wordsXlanguages: 'linkWordLanguageView'
   };
   return this[callMap[App.pageState.getPageViewKey()]](options);
  }
  /**
    Creates the link structure for map view that can be placed in a href attribute.
    Option parameters are {word,languages,study,config}.
  */
, linkMapView: function(options){
    var o = this.sanitize(['Config','Study','Languages','Word'], options);
    //Building route:
    var route = '#/'+o.study+'/map/'+o.word+'/'+o.languages;
    if(_.isString(o.config)){
      route += '/'+o.config;
    }
    return route;
  }
  /**
    Creates the link structure for single word view that can be placed in a href attribute.
    Option parameters are {word,study,config}, all of which are optional.
  */
, linkWordView: function(options){
    var o = this.sanitize(['Config','Study','Word'], options);
    //Building route:
    var route = '#/'+o.study+'/word/'+o.word;
    if(_.isString(o.config)){
      route += '/'+o.config;
    }
    return route;
  }
  /***/
, linkLanguageView: function(options){
    //FIXME implement
    return '#FIXME/implement Router:linkLanguageView';
  }
  /***/
, linkLanguageWordView: function(options){
    //FIXME implement
    return '#FIXME/implement Router:linkLanguageWordView';
  }
  /***/
, linkWordLanguageView: function(options){
    //FIXME implement
    return '#FIXME/implement Router:linkWordLanguageView';
  }
});
