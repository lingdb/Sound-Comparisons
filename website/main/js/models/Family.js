/***/
Family = Backbone.Model.extend({
  initialize: function(){
    //Field for memoization of this familys regions:
    this._regions = null;
  }
  /**
    Generates the FamilyId as used throughout the database.
  */
, getId: function(){
    var studyIx  = this.get('StudyIx')
      , familyIx = this.get('FamilyIx');
    return ''+studyIx+familyIx;
  }
  /**
    Returns a color string in the form of /#{[0-9a-fA-F]}6/, or null.
  */
, getColor: function(){
    var c = this.get('FamilyColorOnWebsite');
    if(typeof(c) === 'string' && c !== ''){
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
    return window.App.translationStorage.translateDynamic(category, field, field);
  }
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
});
