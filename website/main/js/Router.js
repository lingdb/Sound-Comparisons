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
    //FIXME implement
  }
//Link related methods:
  /***/
, linkMapView: function(options){
    var o = this.helpLinkSingleWord(options);
    //Building route:
    var route = '#/'+o.study+'/map/'+o.word;
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
    var o = this.helpLinkSingleWord(options);
    //Building route:
    var route = '#/'+o.study+'/word/'+o.word;
    if(_.isString(o.config)){
      route += '/'+o.config;
    }
    return route;
  }
  /**
    Helper method for link{Map,Word}View, that sanitizes options and handles defaults.
  */
, helpLinkSingleWord: function(options){
    var o = $.extend({
      word:   App.wordCollection.getChoice()
    , study:  App.study
    , config: null
    }, options);
    //Sanitizing:
    if(o.study instanceof Study){
      o.study = o.study.getId();
    }
    if(o.word instanceof Word){
      o.word = o.word.getKey();
    }
    if(o.config !== null){
      o.config = JSON.stringify(o.config);
    }
    return o;
  }
});
