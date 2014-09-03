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
    //pageView routes with config info:
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
  }
});
