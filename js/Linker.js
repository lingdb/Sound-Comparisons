"use strict";
define(['Configurator'], function(Configurator){
  /**
    The Linker extends the Configurator, which in turn extends the Sanitizer.
    Building links makes use of both, the Sanitizer and the Configurator.
    This Class is extended by the Router,
    which is the central link building/route processing entity in our App.
  */
  return Configurator.extend({
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
      //We cannot generate links with options for contributorView:
      , contributorView: 'linkMapView'
     };
     return this[callMap[App.pageState.getPageViewKey()]](options);
    }
    /**
      Creates the link structure for map view that can be placed in a href attribute.
      Option parameters are {word,languages,study,siteLanguage}.
    */
  , linkMapView: function(options){
      var suffixes = ['SiteLanguage','Study','Languages','Word']
        , o = this.sanitize(suffixes, options, 'map');
      return ['#',o.siteLanguage,o.study,'map',o.word,o.languages].join('/');
    }
    /**
      Creates the link structure for single word view that can be placed in a href attribute.
      Option parameters are {word,study,siteLanguage}, all of which are optional.
    */
  , linkWordView: function(options){
      var o = this.sanitize(['SiteLanguage','Study','Word'], options, 'word');
      return ['#',o.siteLanguage,o.study,'word',o.word].join('/');
    }
    /**
      Creates the link structure for single language view that can be placed in a href attribute.
      Option parameters are {siteLanguage,study,language}, all of which are optional.
    */
  , linkLanguageView: function(options){
      var suffixes = ['SiteLanguage','Study','Language']
        , o = this.sanitize(suffixes, options, 'language');
      return ['#',o.siteLanguage,o.study,'language',o.language].join('/');
    }
    /**
      Creates the link structure for languagesXwords view that can be placed in a href attribute.
      Option parameters are {siteLanguage,study,words,languages}, all of which are optional.
    */
  , linkLanguageWordView: function(options){
      var suffixes = ['SiteLanguage','Study','Words','Languages']
        , o = this.sanitize(suffixes, options, 'languagesXwords');
      return ['#',o.siteLanguage,o.study,'languagesXwords',o.languages,o.words].join('/');
    }
    /**
      Creates the link structure for wordsXlanguages view that can be placed in a href attribute.
      Option parameters are {siteLanguage,study,words,languages}, all of which are optional.
    */
  , linkWordLanguageView: function(options){
      var suffixes = ['SiteLanguage','Study','Words','Languages']
        , o = this.sanitize(suffixes, options, 'wordsXlanguages');
      return ['#',o.siteLanguage,o.study,'wordsXlanguages',o.words,o.languages].join('/');
    }
  });
});
