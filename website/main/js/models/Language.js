/***/
Language = Backbone.Model.extend({
  initialize: function(){
    //Field for memoization of this languages rfcLanguage:
    this._rfcLanguage = null; // null -> not tried, undefined -> not found.
    //Field for memoization of languages that have this language as rfcLanguage:
    this._rfcLanguages = null;
    //Field for memoization of regions that have that include this language:
    this._regions = null;
    //Field for memoization of the family that this language belongs to:
    this._family = null;
    //Fields for memoization of the next and prev languages:
    this._next = null;
    this._prev = null;
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
  /**
    Returns the family that this language belongs to, or null.
  */
, getFamily: function(){
    if(this._family === null){
      this._family = this.getRegion().getFamily();
    }
    return this._family;
  }
  /**
    Fetches the next or previous Neighbour of this language,
    according to LanguageIx inside the first Region of a language.
  */
, getNeighbour: function(next){
    //Function to fetch the neighbour element of an array:
    var getN = function(elems, index){
      var delta = next ? 1 : -1
        , count = elems.length;
      //Addition of count so that index >= 0
      index = (index + delta + count) % count;
      return elems[index];
    };
    //Find index of RegionLanguage in models:
    var index  = 0
      , models = App.regionLanguageCollection.models
      , langId = this.get('LanguageIx');
    for(var i = 0; i < models.length; i++){
      if(models[i].get('LanguageIx') === langId){
        index = i;
        break;
      }
    }
    //Find Language belonging to neighbour RegionLanguage:
    var neighbour = getN(models, index)
      , targetId  = neighbour.get('LanguageIx');
    return App.languageCollection.findWhere({LanguageIx: targetId});
  }
  /**
    Fetches the next Neighbour of this language by the means of getNeighbour.
  */
, getNext: function(){
    if(this._next === null){
      this._next = this.getNeighbour(true);
      this._next._prev = this;
    }
    return this._next;
  }
  /**
    Fetches the previous Neighbour of this language by the means of getNeighbour.
  */
, getPrev: function(){
    if(this._prev === null){
      this._prev = this.getNeighbour(false);
      this._prev._next = this;
    }
    return this._prev;
  }
//FIXME name
//FIXME superscript
//FIXME implement getContributors
//FIXME implement further missing methods as I discover the need for them
});
