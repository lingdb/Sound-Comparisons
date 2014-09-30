"use strict";
/***/
var Region = Backbone.Model.extend({
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
    Returns a color string in the form of /#{[0-9a-fA-F]}6/, or null.
  */
, getColor: function(){
    var c = this.get('Color');
    if(typeof(c) === 'string' && c !== ''){
      return '#'+c;
    }
    return null;
  }
  /**
    Returns the Family that the current Region belongs to.
    The field _family is used for memoization.
  */
, getFamily: function(){
    if(this._family === null){
      var regionId = this.getId()
      //_family becomes undefined, if not found:
      this._family = App.familyCollection.find(function(f){
        return regionId.indexOf(f.getId()) === 0;
      });
    }
    return this._family;
  }
  /**
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
});
