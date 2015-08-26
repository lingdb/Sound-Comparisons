"use strict";
define(['backbone','collections/RegionCollection','collections/LanguageCollection'], function(Backbone, RegionCollection, LanguageCollection){
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
      @return regions RegionCollection
      Returns a collection of all regions that belong to this family.
      Finding belonging regions turns out to be kind of tricky.
      Example:
        Family 1 has Id '28'
        Family 2 has Id '20'
        -> All Regions where '28' is prefix belong to Family 1
        -> All Regions where '2' is a prefix, but '28' is not, belong to Family 2
    */
  , getRegions: function(){
      if(!this._regions){
        var familyId = this.getId()//Current familyId
          , regions = [];//[Region]
        //Testing if familyId has a 0 suffix:
        var matches = familyId.match(/^([^0]+)0+$/);
        if(matches !== null){
          //Prefix case:
          //Searching other Ids:
          var otherIds = [];
          App.familyCollection.each(function(f){
            var fId = f.getId();
            if(fId !== familyId){
              otherIds.push(fId);
            }
          });
          //Considering only prefix as current familyId:
          familyId = matches[1];
          //Selecting regions:
          regions = App.regionCollection.filter(function(r){
            var rId = r.getId();
            //Checking that otherIds don't contain a prefix for rId:
            var found = _.any(otherIds, function(oId){
              return rId.indexOf(oId) === 0;
            });
            if(found) return false;
            //Checking that familyId is a prefix for rId:
            return rId.indexOf(familyId) === 0;
          });
        }else{
          //Simple case:
          regions = App.regionCollection.filter(function(r){
              return r.getId().indexOf(familyId) === 0;
            });
        }
        //Setting RegionCollection:
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
