/**
  The router for our application.
  See http://backbonetutorials.com/what-is-a-router for basic info.
*/
Router = Backbone.Router.extend({
  routes: {
    //Basic routes for pageViews:
    ":study/map/:word/:languages":              "mapView"
  , ":study/word/:word":                        "wordView"
  , ":study/language/:language":                "languageView"
  , ":study/languagesXwords/:languages/:words": "languageWordView"
  , ":study/wordsXlanguages/:words/:languages": "wordLanguageView"
  , "*actions":                                 "defaultRoute"
  }
});
