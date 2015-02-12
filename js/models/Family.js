/* global Family: true */
"use strict";
/***/
var Family = Backbone.Model.extend({
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
    var c = this.get('FamilyColorOnWebsite');
    if(_.isString(c) && c !== ''){
      return '#'+c;
    }
    return null;
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
});
