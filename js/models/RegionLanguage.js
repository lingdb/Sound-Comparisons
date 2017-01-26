"use strict";
/* global App */
define(['underscore','backbone','models/Region'], function(_, Backbone, Region){
  /***/
  return Backbone.Model.extend({
    initialize: function(){
      //Field for the memoization of the Language that belongs to this RegionLanguage:
      this._language = null;
      //Field for the memoization of the Region that this RegionLanguage belongs to:
      this._region = null;
    }
    /**
      Returns the Language that belongs to this RegionLanguage.
    */
  , getLanguage: function(){
      if(this._language === null){
        var lId = this.get('LanguageIx');
        this._language = App.languageCollection.findWhere({LanguageIx: lId});
        if(this._language){
          this._language._regionLanguage = this;
        }
      }
      return this._language;
    }
    /**
      The RegionId of a RegionLanguage is produced the same way as the Id of a Region:
    */
  , getRegionId: Region.prototype.getId
    /**
      RegionLanguageCollection has a custom comparator,
      that we use to keep RegionLanguages in order.
      We want to compare RegionLanguages by RegionGpIx first,
      and by RegionMemberLgIx second.
      To achieve this, sortValues returns an array with both values in it.
    */
  , sortValues: function(){
      return [this.get('RegionGpIx'), this.get('RegionMemberLgIx')];
    }
    /***/
  , getRegion: function(){
      if(this._region === null){
        var rId = this.getRegionId()
          , region = App.regionCollection.find(function(r){
            return r.getId() === rId;
          });
        if(!_.isEmpty(region)){
          this._region = region;
        }
      }
      return this._region;
    }
    /**
      Return 'RegionGpMemberLgNameShortInThisSubFamilyWebsite' translated.
    */
  , getLgNameShort: function(){
      var suffix = 'RegionGpMemberLgNameShortInThisSubFamilyWebsite'
        , fb = this.get(suffix);
      if(fb === '') fb = null;
      var field = this.getField(), category = this.getCategory(suffix);
      return App.translationStorage.translateDynamic(category, field, fb);
    }
    /**
      Return 'RegionGpMemberLgNameLongInThisSubFamilyWebsite' translated.
    */
  , getLgNameLong: function(){
      var suffix = 'RegionGpMemberLgNameLongInThisSubFamilyWebsite'
        , fb = this.get(suffix);
      if(fb === '') fb = null;
      var field = this.getField(), category = this.getCategory(suffix);
      return App.translationStorage.translateDynamic(category, field, fb);
    }
    /***/
  , getCategory: function(suffix){
      return 'RegionLanguagesTranslationProvider-RegionLanguages_-Trans_'+suffix;
    }
    /***/
  , getField: function(){
      var study = App.study.get('Name')
        , lIx = this.get('LanguageIx');
      return study+'-'+lIx;
    }
  });
});
