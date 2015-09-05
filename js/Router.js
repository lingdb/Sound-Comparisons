"use strict";
define(['Linker','backbone'], function(Linker, Backbone){
  /**
    The router for our application.
    See http://backbonetutorials.com/what-is-a-router for basic info.
    The Router extends the Linker to gain different methods to create links that match its routes.
    The classes {Sanitizer,Configurator,Linker} where once part of the Router,
    but are now separated due to their different tasks and the want for shorter source files.
  */
  return Linker.extend({
    routes: {
      //mapView:
      ':siteLanguage/:study/map/:word/:languageSelection': 'mapView'
    , ':siteLanguage/:study/map/:word/':                   'mapView'
    , ':siteLanguage/:study/map/':                         'mapView'
    , ':siteLanguage/:study/map':                          'mapView'
      //wordView:
    , ':siteLanguage/:study/word/:word': 'wordView'
    , ':siteLanguage/:study/word/':      'wordView'
    , ':siteLanguage/:study/word':       'wordView'
      //languageView:
    , ':siteLanguage/:study/language/:language': 'languageView'
    , ':siteLanguage/:study/language/':          'languageView'
    , ':siteLanguage/:study/language':           'languageView'
      //languageWordView:
    , ':siteLanguage/:study/languagesXwords/:languageSelection/:wordSelection': 'languageWordView'
    , ':siteLanguage/:study/languagesXwords/:languageSelection/':               'languageWordView'
    , ':siteLanguage/:study/languagesXwords/:languageSelection':                'languageWordView'
    , ':siteLanguage/:study/languagesXwords/':                                  'languageWordView'
    , ':siteLanguage/:study/languagesXwords':                                   'languageWordView'
    , ':siteLanguage/:study/languagesXwords//:wordSelection':                   'languageWordView_' // Flipped parameters
      //wordLanguageView:
    , ':siteLanguage/:study/wordsXlanguages/:wordSelection/:languageSelection': 'wordLanguageView'
    , ':siteLanguage/:study/wordsXlanguages/:wordSelection/':                   'wordLanguageView'
    , ':siteLanguage/:study/wordsXlanguages/:wordSelection':                    'wordLanguageView'
    , ':siteLanguage/:study/wordsXlanguages/':                                  'wordLanguageView'
    , ':siteLanguage/:study/wordsXlanguages':                                   'wordLanguageView'
    , ':siteLanguage/:study/wordsXlanguages//:languageSelection':               'wordLanguageView_' // Flipped parameters
      //contributorView:
    , 'Contributors/:initials': 'contributorView'
    , 'Contributors/':          'contributorView'
    , 'Contributors':           'contributorView'
      //Routes for configuration directives:
    , 'config/*directives': 'configDirective'
      //defaultRoute:
    , '*actions':           'defaultRoute'
    }
  , initialize: function(){
      //The Router processes the config directives:
      this.on('route:configDirective', function(){
        var directives = _.last(arguments);
        //Applying configuration directives:
        this.configure(directives);
        //Rendering page:
        App.views.renderer.render();
      }, this);
      /**
        The Router handles shortLinks and triggers navigating them.
        We don't want the URL to change, but we'd like the router to act as if it changed.
        https://stackoverflow.com/questions/17334465/backbone-js-routing-without-changing-url
      */
      this.on('route:shortLink', function(shortLink){
        //Searching shortLink in the according map:
        var slMap = App.dataStorage.getShortLinksMap();
        if(shortLink in slMap){
          var url = slMap[shortLink]
            , matches = url.match(/^[^#]*#(.+)$/)
            , fragment = (matches) ? matches[1] : url;
          Backbone.history.loadUrl(fragment);
        }else{
          //We can still remain where we are:
          console.log('Could not route shortLink: '+shortLink);
          App.views.renderer.render();
        }
      }, this);
      /*
        The defaultRoute provides detection as described in
        https://github.com/sndcomp/website/issues/188
      */
      this.on('route:defaultRoute', function(route){
        console.log('Router.defaultRoute('+route+')');
        if(_.isString(route)){//route may also be null…
          //Route parts when splitting route by '/' and than by ',':
          var parts = _.flatten(_.map(route.split('/'), function(p){
            return p.split(',');
          }));
          //Parts that can be changed:
          var toChange = {
            siteLanguage: null//String || null
          , study: null//String || null
            //languages will be filled via {iso,glotto}code and language detection.
          , languages: []//[Language]
            //words will be filled by word detection.
          , words: []//[Word]
            //pageView if detection decides to set it
          , pageView: null//String
          };
          //Running detection:
          _.each(parts, function(part){
            //Detection for siteLanguage:
            if(toChange.siteLanguage === null){
              if(App.translationStorage.isBrowserMatch(part)){
                toChange.siteLanguage = part;
                return;//Stop detection for current part
              }
            }
            //Detection for study:
            if(toChange.study === null){
              if(_.contains(App.study.getAllIds(), part)){
                toChange.study = part;
                return;//Stop detection for current part
              }
            }
            //Detection for iso code:
            var lang = App.languageCollection.getLanguageByIso(part);
            if(lang !== null){
              toChange.languages.push(lang);
              return;//Stop detection for current part
            }
            //Detection for glotto codes:
            lang = App.languageCollection.getLanguageByGlotto(part);
            if(lang !== null){
              toChange.languages.push(lang);
              return;//Stop detection for current part
            }
            //FIXME what about detection of language/word names?
            //Detection of pageViewKeys:
            var pv = App.pageState.validatePageViewKey(part);
            if(pv !== null){
              toChange.pageView = pv;
              return;//Stop detection for current part
            }
          }, this);
          //Converting selections to choices if possible:
          _.each([['languages','language'],['words','word']], function(pair){
            var selection = pair[0], choice = pair[1]
              , length = toChange[selection].length;
            //Empty or single element selections:
            if(length <= 1){
              if(length === 1){
                toChange[choice] = _.head(toChange[selection]);
              }
              //Removing useless selection:
              delete toChange[selection];
            }
          }, this);
          //FIXME what about detection for pageViewKeys?
          //Removing useless keys from toChange:
          _.each(_.keys(toChange), function(key){
            if(_.isEmpty(toChange[key])){
              delete toChange[key];
            }
          }, this);
          //Applying toChange:
          this.configure(toChange).always(function(){
            App.views.renderer.render();
          });
        }else{
          //Making sure something is rendered:
          App.views.renderer.render();
        }
      }, this);
    }
    /**
      Updates the fragment without triggering.
      This is useful to refresh the URL,
      and bring it up to the current state.
      Usually we call this from Renderer.render.
    */
  , updateFragment: function(){
      var fragment = this.linkCurrent({shortSelections: true});
      this.navigate(fragment, {trigger: false, replace: true});
      App.study.trackLinks(fragment);
    }
  });
});
