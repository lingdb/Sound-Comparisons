/**
  The router for our application.
  See http://backbonetutorials.com/what-is-a-router for basic info.
  The Router extends the Linker to gain different methods to create links that match its routes.
  The classes {Sanitizer,Configurator,Linker} where once part of the Router,
  but are now separated due to their different tasks and the want for shorter source files.
*/
Router = Linker.extend({
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
    //Various other routes:
  , 'whoAreWe':                                         "contributorView"
  , 'whoAreWe/:initials':                               "contributorView"
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
      var c = r+'Config';
      this.on('route:'+c, function(){
        //Process configuration:
        this.configure(_.last(arguments));
        //Triger non config route:
        var forward = _.take(arguments, arguments.length - 1);
        forward.unshift('route:'+r);
        this.trigger.apply(this, forward);
      }, this);
    }, this);
  }
});
