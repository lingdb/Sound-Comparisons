"use strict";
define(['backbone'], function(Backbone){
  /**
    The PageState has a variety of tasks that lie at the core of our Application.
    - It tracks state for the site, where the different parts should not do so themselfs.
    - It aids construcing links for the site.
    - It assists in parsing links for the site.
  */
  return Backbone.Model.extend({
    defaults: {
      wordOrder: 'logical'
    , spLang: null
    , phLang: null
    , pageView: 'map'
    , pageViews: ['map'
                 ,'word'
                 ,'language'
                 ,'languagesXwords'
                 ,'wordsXlanguages'
                 ,'contributorView'
                 ,'aboutView']
    , pageViewShortcuts: {
        'm':  'map'
      , 'w':  'word'
      , 'l':  'language'
      , 'lw': 'languagesXwords'
      , 'wl': 'wordsXlanguages'
      , 'c':  'contributorView'
      , 'a':  'aboutView'
      }
    , mapViewIgnoreSelection: false // On true all languages shall be displayed
    , wordByWord: false // Should wordByWord downloads be displayed?
    , wordByWordFormat: 'mp3' // Initially set by views/AudioLogic
    }
    /**
      Sets up callbacks to manipulate PageState when necessary.
    */
  , activate: function(){
      //{Sp,Ph}Lang need resetting on study change:
      App.study.on('change', this.resetLangs, this);
      //{Sp,Ph}Lang need updating on translation change:
      App.translationStorage.on('change:translationId', this.translationChanged, this);
    }
  //Managing the wordOrder:
    /**
      Predicate to test if the wordOrder is logical
    */
  , wordOrderIsLogical: function(){
      return this.get('wordOrder') === 'logical';
    }
    /**
      Predicate to test if the wordOrder is alphabetical
    */
  , wordOrderIsAlphabetical: function(){
      return this.get('wordOrder') === 'alphabetical';
    }
    /**
      Sets the wordOrder to logical
    */
  , wordOrderSetLogical: function(){
      this.set({wordOrder: 'logical'});
    }
    /**
      Sets the wordOrder to alphabetical
    */
  , wordOrderSetAlphabetical: function(){
      this.set({wordOrder: 'alphabetical'});
    }
  //Managing {sp,ph}Lang:
    /**
      @return spl Language || null
      Returns the current spellingLanguage
    */
  , getSpLang: function(){
      var spl = this.get('spLang');
      if(spl === null){
        spl = App.translationStorage.getRfcLanguage();
        this.attributes.spLang = spl;
      }
      return spl;
    }
    /***/
  , setSpLang: function(l){
      this.set({spLang: l || null});
    }
    /**
      @return phl Language || null
      Returns the current phoneticLanguage
    */
  , getPhLang: function(){
      var phl = this.get('phLang');
      if(phl === null){
        var spl = this.getSpLang();
        if(spl){
          phl = spl;
        }else{
          phl = App.languageCollection.getDefaultPhoneticLanguage() || null;
        }
        this.attributes.phLang = phl;
      }
      return phl;
    }
    /***/
  , setPhLang: function(l){
      this.set({phLang: l || null});
    }
    /**
      This method will be called on change:translationId from TranslationStorage.
      It's objective is to set {Sp,Ph}Lang according to the new translationId.
    */
  , translationChanged: function(){
      var l = App.translationStorage.getRfcLanguage();
      if(l !== null){
        this.attributes.spLang = l;
        this.attributes.phLang = l;
      }else{
        this.resetLangs();
      }
    }
    /**
      This function resets the {Sp,Ph}Lang properties of the PageState.
      It will normaly be called on study change.
    */
  , resetLangs: function(){
      this.set({spLang: null, phLang: null});
    }
  //Managing pageView:
    /**
      Predicate to tell if the current pageView is a multiView.
    */
  , isMultiView: function(pvk){
      pvk = pvk || this.get('pageView');
      return _.contains(['languagesXwords','wordsXlanguages'], pvk);
    }
    /**
      Predicate to tell if the current pageView is the mapView.
    */
  , isMapView: function(pvk){
      pvk = pvk || this.get('pageView');
      return pvk === 'map';
    }
    /**
      Returns the currently active pageView as a Backbone.View
    */
  , getPageView: function(){
      var pvMap = {
        map:             'mapView'
      , word:            'wordView'
      , language:        'languageView'
      , languagesXwords: 'languageWordView'
      , wordsXlanguages: 'wordLanguageView'
      }, key = this.get('pageView');
      return App.views.renderer.model[pvMap[key]];
    }
    /**
      Returns the key for the current PageView.
    */
  , getPageViewKey: function(){return this.get('pageView');}
    /**
      Changes the current pageView to a given String or Backbone.View.
      Instances of Backbone.View are required to have a getKey method.
    */
  , setPageView: function(pv){
      if(_.isString(pv)){
        if(_.contains(this.get('pageViews'), pv)){
          this.set({pageView: pv});
        }else{
          console.log('PageState.setPageView() refuses to set pageView: '+pv);
        }
      }else if(pv instanceof Backbone.View){
        if(_.isFunction(pv.getKey)){
          this.setPageView(pv.getKey());
        }
      }else{
        console.log('PageState.setPageView() failed:');
        console.log(pv);
      }
    }
    /**
      Tells wether the given String or Backbone.View is the current PageView.
      Instances of Backbone.View are required to have a getKey method.
    */
  , isPageView: function(key){
      if(_.isString(key)){
        if(_.contains(this.get('pageViews'), key)){
          return this.get('pageView') === key;
        }
        var shortcuts = this.get('pageViewShortcuts');
        if(key in shortcuts){
          return this.isPageView(shortcuts[key]);
        }
      }else if(key instanceof Backbone.View){
        if(!_.isFunction(key.getKey))
          return false;
        return this.isPageView(key.getKey());
      }else if(_.isArray(key)){
        return _.some(key, this.isPageView, this);
      }
      console.log('PageState.isPageState() with unexpected key: '+key);
      return false;
    }
    /**
      @param key String
      @return is String || null
      Tests if the given key is a valid key for a PageView.
      This is useful for detection in Router.
    */
  , validatePageViewKey: function(key){
      if(!_.isString(key)) return null;
      var shortcuts = this.get('pageViewShortcuts');
      if(key in shortcuts) return shortcuts[key];
      if(_.contains(this.get('pageViews'), key)) return key;
      return null;
    }
  });
});
