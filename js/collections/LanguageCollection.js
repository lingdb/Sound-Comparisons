"use strict";
define(['collections/Choice', 'models/Language'], function(Choice, Language){
  /***/
  return Choice.extend({
    model: Language
    /**
      LanguageCollection get's its own initialize so that it can handle language keys on reset.
      The problem here is, that the ShortName of a language may not always be enough to identify it.
      For the language to decide if the ShortName is enough, it needs to know if another language has the same key.
      Therefore we build a map of ShortNames to counts, that will be accessible for languages.
      To support this, the LanguageCollection offers a 'shortNameCount' method.
    */
  , initialize: function(){
      this.shortNameMap = null;
      this.on('reset', this.countShortNames, this);
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
    /***/
  , getDefaultPhoneticLanguage: function(){
      return this.find(function(l){
        return l.isDefaultPhoneticLanguage() || false;
      });
    }
    /***/
  , getSpellingLanguages: function(){
      if(!this._spellingLanguages){
        var langs = this.filter(function(l){
          return parseInt(l.get('IsSpellingRfcLang')) === 1;
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
                          : App.defaults.getLanguages();
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
});
