"use strict";
define(['backbone'], function(Backbone){
  /***/
  return Backbone.Model.extend({
    initialize: function(){
      //Field for memoization of this familys regions:
      this._regions = null;
      //Field for memoization of this familys languages:
      this._languages = null;
    }
    /**
      Generates the FamilyId as used throughout the database.
    */
  , getId: function(){
      var studyIx  = this.get('StudyIx')
        , familyIx = this.get('FamilyIx');
      return ''+studyIx+familyIx;
    }
    /***/
  , getKey: function(){return this.get('FamilyNm');}
    /**
      Returns a color string in the form of /#{[0-9a-fA-F]}6/, or null.
    */
  , getColor: function(){
      var ix = 0, t = this;
      App.familyCollection.find(function(x, i){
        ix = i; return x === t;
      });
      return App.colors.getColor(ix);
    }
    /**
      Returns the name for the current family in the current translation.
    */
  , getName: function(){
      var category = 'FamilyTranslationProvider'
        , field    = this.get('FamilyNm');
      return App.translationStorage.translateDynamic(category, field, field);
    }
    /**
      Returns a collection of all regions that belong to this family.
    */
  , getRegions: function(){
      if(!this._regions){
        var familyId = this.getId()
          , regions  = App.regionCollection.filter(function(r){
            return r.getId().indexOf(familyId) === 0;
          });
        this._regions = new RegionCollection(regions);
      }
      return this._regions;
    }
    /**
      Returns a collection of all languages that belong to this family.
    */
  , getLanguages: function(){
      if(this._languages === null){
        var lSet = {}; // LanguageId -> Language
        this.getRegions().each(function(r){
          r.getLanguages().each(function(l){
            lSet[l.getId()] = l;
          });
        });
        this._languages = new LanguageCollection(_.values(lSet));
      }
      return this._languages;
    }
    /**
      Check if there are any languages in this family.
    */
  , hasLanguages: function(){
      var rCol = this.getRegions();
      for(var i = 0; i < rCol.models.length; i++){
        var r = rCol.models[i];
        if(r.getLanguages().length > 0){
          return true;
        }
      }
      return false;
    }
  });
});
