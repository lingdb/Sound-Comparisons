"use strict";
define(['collections/Choice', 'models/Language', 'collections/RegionLanguageCollection', 'collections/Selection'], function(Choice, Language, RegionLanguageCollection, Selection){
  /***/
  var LanguageCollection = Choice.extend({
    model: Language
    /**
      LanguageCollection get's its own initialize so that it can handle language keys on reset.
      The problem here is, that the ShortName of a language may not always be enough to identify it.
      For the language to decide if the ShortName is enough, it needs to know if another language has the same key.
      Therefore we build a map of ShortNames to counts, that will be accessible for languages.
      To support this, the LanguageCollection offers a 'shortNameCount' method.
      With #188 it's also necessary to find languages by their iso and glottocodes.
      To aid this we have the isoCodeMap and the glottoCodeMap.
    */
  , initialize: function(){
      //ShortNameMap :: ShortName -> Int:
      this.shortNameMap = null;
      this.on('reset', this.countShortNames, this);
      //isoCodeMap :: iso code -> Language
      this.isoCodeMap = null;
      this.on('reset', this.mapIsoCodes, this);
      //glottoCodeMap :: glotto code -> Language
      this.glottoCodeMap = null;
      this.on('reset', this.mapGlottoCodes, this);
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
      Builds the isoCodeMap that maps iso codes to their languages.
      If multiple languages carry the same iso code,
      than the last write will win.
    */
  , mapIsoCodes: function(){
      this.isoCodeMap = {};
      this.each(function(l){
        var iso = l.getISO();
        if(iso !== null){
          this.isoCodeMap[iso] = l;
        }
      }, this);
    }
    /**
      Builds the glottoCodeMap that maps glotto codes to their languages.
      If multiple languages carry the same glotto code,
      than the last write will win.
    */
  , mapGlottoCodes: function(){
      this.glottoCodeMap = {};
      this.each(function(l){
        var gc = l.getGlottoCode();
        if(gc !== null){
          this.glottoCodeMap[gc] = l;
        }
      }, this);
    }
    /**
      @param iso String iso code
      @return language Language || null
      Tries to fetch a Language from the isoCodeMap.
    */
  , getLanguageByIso: function(iso){
      return this.isoCodeMap[iso] || null;
    }
    /**
      @param glotto String glotto code
      @return language Language || null
      Tries to fetch a Language from the glottoCodeMap.
    */
  , getLanguageByGlotto: function(glotto){
      return this.glottoCodeMap[glotto] || null;
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
          delete this._spellingLanguages;
        }
        this.reset(data.languages);
      }
    }
    /**
      @return default Language
      As of #304 this method returns the same result as getDefaultChoice().
    */
  , getDefaultPhoneticLanguage: function(){
      return this.getDefaultChoice();
    }
    /***/
  , getSpellingLanguages: function(){
      if(!this._spellingLanguages){
        var langs = this.filter(function(l){
          return l.isSpellingLanguage();
        }, this);
        this._spellingLanguages = new LanguageCollection(langs);
      }
      return this._spellingLanguages;
    }
    /**
      Returns the default Languages as array to be used as selection for the LanguageCollection.
      Note that this method depends on the current PageView.
    */
  , getDefaultSelection: function(pvk){
      if(_.isString(pvk) && !_.isEmpty(pvk)){
        pvk = pvk || App.pageState.getPageViewKey();
        var isMap = App.pageState.isMapView(pvk)
          , sel   = isMap ? App.defaults.getMapLanguages()
                          : App.defaults.getLanguages({'pageViewKey': pvk});
        if(sel.length === 0){
          return _.take(this.models, 5);
        }
        return sel;
      }else{
        var ret = {};
        _.each(App.pageState.get('pageViews'), function(pvk){
          ret[pvk] = this.getDefaultSelection(pvk);
        }, this);
        return ret;
      }
    }
    /**
      Returns the default Language to be used as Choice for the LanguageCollection.
    */
  , getDefaultChoice: function(){
      return App.defaults.getLanguage();
    }
    /**
      Sort languages after their RegionLanguages, iff possible.
      Otherwise sort them after their LanguageIx.
    */
  , comparator: function(a, b){
      var arl = a.getRegionLanguage()
        , brl = b.getRegionLanguage();
      if(_.isEmpty(arl) || _.isEmpty(brl)){
        var aIx = a.get('LanguageIx')
          , bIx = b.get('LanguageIx');
        if(aIx > bIx) return  1;
        if(aIx < bIx) return -1;
        return 0;
      }
      return RegionLanguageCollection.prototype.comparator(arl,brl);
    }
    /**
      Wrapper for Selection.getSelected,
      that makes sure to sort returned array as expeceted.
    */
  , getSelected: function(){
      var sel = Selection.prototype.getSelected.apply(this, arguments);
      sel.sort(this.comparator);
      return sel;
    }
  });
  return LanguageCollection;
});
