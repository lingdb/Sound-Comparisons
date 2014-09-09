/**
  The router for our application.
  See http://backbonetutorials.com/what-is-a-router for basic info.
*/
Router = Backbone.Router.extend({
  routes: {
    //Basic routes for pageViews:
    ":study/map/:word/:languages":                      "mapView"
  , ":study/word/:word":                                "wordView"
  , ":study/language/:language":                        "languageView"
  , ":study/languagesXwords/:languages/:words":         "languageWordView"
  , ":study/wordsXlanguages/:words/:languages":         "wordLanguageView"
    //pageView routes with config info (config routes):
  , ":study/map/:word/:languages/*config":              "mapViewConfig"
  , ":study/word/:word/*config":                        "wordViewConfig"
  , ":study/language/:language/*config":                "languageViewConfig"
  , ":study/languagesXwords/:languages/:words/*config": "languageWordViewConfig"
  , ":study/wordsXlanguages/:words/:languages/*config": "wordLanguageViewConfig"
    //Route for missing implementations of links:
  , "FIXME":                                            "missingRoute"
  , "FIXME/*infos":                                     "missingRoute"
    //Catch all route:
  , "*actions":                                         "defaultRoute"
  }
, initialize: function(){
    //The Router looks for missing routes itself:
    this.on('route:missingRoute', function(infos){
      if(infos){
        console.log('Router found missing route with infos: '+infos);
      }else{
        console.log('Router found missing route.');
      }
    }, this);
    /**
      The Router acts as a proxy in that it processes all config routes,
      and afterwards triggers the basic routes.
    */
    var configRoutes = ["mapView", "wordView", "languageView", "languageWordView", "wordLanguageView"];
    _.each(configRoutes, function(r){
      this.on('route:'+r+'Config', function(){
        //Process configuration:
        this.configure(_.last(arguments));
        //Triger non config route:
        this.trigger(r, _.take(arguments, arguments.length - 1));
      }, this);
    }, this);
  }
//Configuration related methods:
  /**
    This method shall modify different page settings that can be conveyed via the config routes.
  */
, configure: function(config){
    console.log('Router.configure()');
    //Sanitizing config:
    if(_.isString(config)){
      config = $.parseJSON(config);
    }
    //Configuring the wordOrder:
    if('wordOrder' in config){
      var callMap = { alphabetical: 'wordOrderSetAlphabetical'
                    , logical:      'wordOrderSetLogical'};
      if(config.wordOrder in callMap){
        App.pageState[callMap[config.wordOrder]]();
      }else{
        console.log('Could not configure wordOrder: '+config.wordOrder);
      }
    }
    if('spLang' in config){
      var spLang = App.languageCollection.find(function(l){
        return l.getKey() === config.spLang;
      }, this);
      App.pageState.setSpLang(spLang);
    }
    if('phLang' in config){
      var phLang = App.languageCollection.find(function(l){
        return l.getKey() === config.phLang;
      }, this);
      App.pageState.setPhLang(phLang);
    }
    if('meaningGroups' in config){
      var lookup = {};
      _.each(config.meaningGroups, function(m){lookup[m] = true;});
      var mgs = App.meaningGroupCollection.filter(function(m){
        return m.getKey() in lookup;
      }, this);
      App.meaningGroupCollection.setSelected(mgs);
    }
    //FIXME Add other configuration cases.
  }
  /**
    Takes a calls Object that maps Suffixes to args,
    where Suffix is the match of /configSet(.+)/ for methods of Router,
    and args will be applied to the Router method,
    with the config as first argument.
    If args is not an array, but a single value,
    that value will be wrapped in an array,
    and, after prepending the config, become the second argument.
    Router:sanitize works in a similar fashion.
  */
, configSet: function(calls, config){
    config = config || {};
    _.each(calls, function(args, suffix){
      var method = 'configSet'+suffix;
      if(method in this){
        if(!_.isArray(args)){
          args = [args];
        }
        args.unshift(config);
        config = this[method].apply(this, args);
      }else{
        console.log('Router:configSet() cannot call method: '+method);
      }
    }, this);
    return config;
  }
  /**
    Builds configuration to set the WordOrder managed by PageState to alphabetical.
  */
, configSetWordOrderAlphabetical: function(config){
    config = config || {};
    config.wordOrder = 'alphabetical';
    return config;
  }
  /**
    Builds configuration to set the WordOrder managed by PageState to logical.
  */
, configSetWordOrderLogical: function(config){
    config = config || {};
    config.wordOrder = 'logical';
    return config;
  }
  /**
    Builds configuration to set the spLang to the given spLang.
  */
, configSetSpLang: function(config, spLang){
    config = config || {};
    if(spLang instanceof Language){
      config.spLang = spLang.getKey();
    }
    return config;
  }
  /**
    Builds configuration to set the phLang to the given phLang.
  */
, configSetPhLang: function(config, phLang){
    config = config || {};
    if(phLang instanceof Language){
      config.phLang = phLang.getKey();
    }
    return config;
  }
  /***/
, configSetMeaningGroups: function(config, mgs){
    config = config || {};
    if(mgs instanceof MeaningGroupCollection){
      mgs = mgs.models;
    }
    if(_.isArray(mgs)){
      var ms = _.map(mgs, function(mg){
        if(_.isString(mg)) return mg;
        return mg.getKey();
      }, this);
      config.meaningGroups = JSON.stringify(ms);
    }
    return config;
  }
//Link related methods:
  /**
    Produces a config with configSet, and builds a link with linkCurrent.
    This shortens the code at several other places.
  */
, linkConfig: function(calls){
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
//Sanitize methods that aid building the links:
  /**
    A proxy for the other sanitize methods.
    It chains all sanitize methods with the given suffixes,
    threading the given object trough all of them,
    to finally return the sanitized version.
  */
, sanitize: function(suffixes, o){
    _.each(suffixes, function(s){
      var key = 'sanitize'+s;
      if(key in this){
        o = this[key](o);
      }else{
        console.log('Router.sanitize() cannot sanitize with key: '+key);
      }
    }, this);
    return o;
  }
  /***/
, sanitizeConfig: function(o){
    if('config' in o){
      if(o.config !== null){
        o.config = JSON.stringify(o.config);
      }
    }else{
      o.config = null;
    }
    return o;
  }
  /***/
, sanitizeLanguage: function(o){
    if(!('language' in o)){
      o.language = App.languageCollection.getChoice();
    }
    if(o.language instanceof Language){
      o.language = o.language.getKey();
    }
    return o;
  }
  /***/
, sanitizeLanguages: function(o){
    if(!('languages' in  o)){
      o.languages = App.languageCollection.getSelected();
    }
    if(o.languages instanceof Backbone.Collection){
      o.languages = o.languages.models;
    }
    if(_.isArray(o.languages)){
      var ls = _.map(o.languages, function(l){
        if(_.isString(l)) return l;
        return l.getKey();
      }, this);
      o.languages = JSON.stringify(ls);
    }
    return o;
  }
  /***/
, sanitizeStudy: function(o){
    if(!('study') in o){
      o.study = App.study;
    }
    if(o.study instanceof Study){
      o.study = o.study.getId();
    }
    return o;
  }
  /***/
, sanitizeWord: function(o){
    if(!('word' in o)){
      o.word = App.wordCollection.getChoice();
    }
    if(o.word instanceof Word){
      o.word = o.word.getKey();
    }
    return o;
  }
  /***/
, sanitizeWords: function(o){
    if(!('words' in o)){
      o.words = App.wordCollection.getSelected();
    }
    if(o.words instanceof Backbone.Collection){
      o.words = o.words.models;
    }
    if(_.isArray(o.words)){
      var ws = _.map(o.words, function(w){
        if(_.isString(w)) return w;
        return w.getKey();
      }, this);
      o.words = JSON.stringify(ws);
    }
    return o;
  }
});
