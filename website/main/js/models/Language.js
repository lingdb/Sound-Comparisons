/***/
Language = Backbone.Model.extend({
  initialize: function(){
    //Field for memoization of this languages rfcLanguage:
    this._rfcLanguage = null; // null -> not tried, undefined -> not found.
    //Field for memoization of languages that have this language as rfcLanguage:
    this._rfcLanguages = null;
    //Field for memoization of regions that have that include this language:
    this._regions = null;
  }
  /**
    Returns the RfcLanguage for the current Language.
    The field _rfcLanguage is used for memoization.
  */
, getRfcLanguage: function(){
    if(this._rfcLanguage === null){
      var rfcId = this.get('RfcLanguage');
      if(typeof(rfcId) === 'string'){
        var rs = App.languageCollection.where({LanguageIx: rfcId});
        this._rfcLanguage = rs[0];
      }else{
        // We cannot find an RfcLanguage:
        this._rfcLanguage = undefined;
      }
    }
    return this.rfcLanguage;
  }
  /**
    Returns a Collection of all Languages that have this language as their RfcLanguage.
  */
, getRfcLanguages: function(){
    if(this._rfcLanguages === null){
      var ls = App.languageCollection.where({RfcLanguage: this.get('LanguageIx')});
      this._rfcLanguages = new LanguageCollection(ls);
    }
    return this._rfcLanguages;
  }
  /**
    Predicate to determine, if this language is the rfcLanguage of another language.
  */
, isRfcLanguage: function(){
    var rls = this.getRfcLanguages();
    return rls.length > 0;
  }
  /**
    Returns a Collection of Regions that this Language is contained in.
  */
, getRegions: function(){
    if(this._regions === null){
      this._regions = App.regionLanguageCollection.findRegions(this);
    }
    return this._regions;
  }
  /**
    Returns the first region that this language is contained in, or null.
  */
, getRegion: function(){
    var ms = this.getRegions().models;
    if(ms.length > 0)
      return ms[0];
    return null;
  }
//FIXME relation with family
//FIXME name
//FIXME superscript
});
