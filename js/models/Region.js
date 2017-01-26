"use strict";
/* global App */
define(['underscore','backbone'], function(_, Backbone){
  /***/
  return Backbone.Model.extend({
    initialize: function(){
      //Field for memoization of this regions family:
      this._family = null;
      //Field for memoization of this regions languages:
      this._languages = null;
    }
    /**
      Generates the RegionId as used troughout the database.
    */
  , getId: function(){
      var studyIx     = this.get('StudyIx')
        , familyIx    = this.get('FamilyIx')
        , subFamilyIx = this.get('SubFamilyIx')
        , regionGpIx  = this.get('RegionGpIx');
      return ''+studyIx+familyIx+subFamilyIx+regionGpIx;
    }
    /***/
  , getKey: function(){
      return this.get('RegionGpNameShort');
    }
    /**
      Method to build the field name necessary for translation by using getId and the current study.
    */
  , getField: function(){
      var study    = App.study.get('Name')
        , regionId = this.getId();
      return study+'-'+regionId;
    }
    /**
      Method to build the category name necessary for translation.
    */
  , getCategory: function(suffix){
      return 'RegionsTranslationProvider-Regions_-Trans_'+suffix;
    }
    /**
      Returns the shortName for the current region in the current translation.
    */
  , getShortName: function(){
      var category = this.getCategory('RegionGpNameShort')
        , field    = this.getField()
        , fallback = this.get('RegionGpNameShort');
      return App.translationStorage.translateDynamic(category, field, fallback);
    }
    /**
      Returns the longName for the current region in the current translation.
    */
  , getLongName: function(){
      var category = this.getCategory('RegionGpNameLong')
        , field    = this.getField()
        , fallback = this.get('RegionGpNameLong');
      return App.translationStorage.translateDynamic(category, field, fallback);
    }
    /**
      @return color String || null
      Tries to find the color for a Region.
      Color strings are expected to match /#{[0-9a-fA-F]}6/
    */
  , getColor: function(){
      if(App.study.getColorByFamily()){
        var f = this.getFamily();
        if(f !== null){
          return f.getColor();
        }
      }
      var rIx = this.get('RegionGpIx');
      if(_.isString(rIx)){
        rIx = parseInt(rIx);
      }
      if(_.isNumber(rIx)){
        return App.colors.getColor(rIx);
      }
      return null;
    }
    /**
      @return family Family || null
      Returns the Family that the current Region belongs to.
      The field _family is used for memoization.
    */
  , getFamily: function(){
      if(this._family === null){
        var regionId = this.getId()
          , family // Family
          , score = 0;//Score for the current Family.
        //Iterating families:
        App.familyCollection.each(function(f){
          var fId = f.getId()//Id of the current family.
            , matches = fId.match(/^([^0]+)0+$/);//Possible prefix for family.
          if(matches !== null){
            fId = matches[1];//Reducing fId to prefix, iff necessary.
          }
          //Keeping matches with higher scores.
          if(regionId.indexOf(fId) === 0){
            if(fId.length > score){
              family = f;
              score = fId.length;
            }
          }
        });
        //Checking for undefined:
        if(!_.isUndefined(family)){
          this._family = family;
        }
      }
      return this._family;
    }
    /**
      @return languages LanguageCollection
      Returns the languages that belong to the current Region.
      The field _languages is used for memoization.
    */
  , getLanguages: function(){
      if(this._languages === null){
        this._languages = App.regionLanguageCollection.findLanguages(this);
      }
      return this._languages;
    }
    /**
      A Region is historical, iff one of it's fields contains the substring 'Historical'.
    */
  , isHistorical: function(){
      var regex  = /Historical/
        , fields = this.pick('RegionGpNameShort', 'RegionGpNameLong');
      return _.any(fields, function(f){
        if(f.match(regex))
          return true;
        return false;
      }, this);
    }
    /**
      @return expanded Bool
      Get the DefaultExpandedState of a Region as a boolean.
    */
  , getDefaultExpandedState: function(){
      var expanded = this.get('DefaultExpandedState');
      return expanded == 1;
    }
  });
});
