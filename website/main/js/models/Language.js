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
    //Field for memoization of the RegionLanguage for this language:
    this._regionLanguage = null;
    //Field for memoization of the LanguageStatusType for this language:
    this._languageStatusType = null;
    //Field for memoization of contributors:
    this._contributors = null;
  }
  /***/
, getId: function(){return this.get('LanguageIx');}
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
    Returns the RegionLanguage that this Language belongs to.
  */
, getRegionLanguage: function(){
    if(this._regionLanguage === null){
      var lId = this.get('LanguageIx');
      this._regionLanguage = App.regionLanguageCollection.findWhere({LanguageIx: lId});
      if(this._regionLanguage){
        this._regionLanguage._language = this;
      }
    }
    return this._regionLanguage;
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
    //Return Language belonging to neighbour RegionLanguage:
    return getN(models, index).getLanguage();
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
  /**
    Helper method to produce the category necessary to fetch the dynamic translation.
  */
, getCategory: function(suffix){
    return 'LanguagesTranslationProvider-Languages_-Trans_'+suffix;
  }
  /**
    Helper method to produce the field necessary to fetch the dynamic translation.
  */
, getField: function(){
    return ''+App.study.get('Name')+'-'+this.get('LanguageIx');
  }
  /**
    Returns the short name of the current language in the current translation.
  */
, getShortName: function(){
    var field    = this.getField()
      , suffixes = [ 'RegionGpMemberLgNameShortInThisSubFamilyWebsite'
                   , 'RegionGpMemberLgNameLongInThisSubFamilyWebsite'
                   , 'ShortName' ];
    for(var i = 0; i < suffixes.length; i++){
      var category = this.getCategory(suffixes[i])
        , trans    = App.translationStorage.translateDynamic(category, field, null);
      if(trans !== null)
        return trans;
    }
    return this.get('ShortName');
  }
  /**
    Returns the long name of the current language in the current translation.
    If no long name is found, this function falls back to getShortName.
  */
, getLongName: function(){
    var category = this.getCategory('RegionGpMemberLgNameLongInThisSubFamilyWebsite')
      , field    = this.getField()
      , trans    = App.translationStorage.translateDynamic(category, field, null);
    if(trans === null){
      var rl = this.getRegionLanguage();
      if(rl){
        trans = this.getRegionLanguage().get('RegionGpMemberLgNameLongInThisSubFamilyWebsite');
      }else{
        console.log('Language.getLongName(): no RegionLanguage for LanguageIx: '+this.get('LanguageIx'));
      }
      if(!trans || trans === ''){
        trans = this.getShortName();
      }
    }
    return trans;
  }
  /**
    Returns the LanguageStatusType connected with this Language.
  */
, getLanguageStatusType: function(){
    if(this._languageStatusType === null){
      var query = {LanguageStatusType: this.get('LanguageStatusType')};
      this._languageStatusType = App.languageStatusTypeCollection.findWhere(query);
    }
    return this._languageStatusType;
  }
  /**
    Returns the Superscript for this Language, with fields in the current translation.
    The target attribute can be speficied as a parameter, and will default to null.
  */
, getSuperscript: function(target){
    var lst = this.getLanguageStatusType();
    return {
      target: target || null
    , ttip: lst.getStatusTooltip()
    , superscript: lst.getStatus()
    , isSuper: true
    };
  }
  /**
    Returns an array of all possible fields in the Object returned by getContributors.
    Field are in the order they should be displayed in.
  */
, getContributorFields: function(){
    return [ 'ContributorSpokenBy'
           , 'ContributorRecordedBy1'
           , 'ContributorRecordedBy2'
           , 'ContributorSoundEditingBy'
           , 'ContributorPhoneticTranscriptionBy'
           , 'ContributorReconstructionBy'
           , 'ContributorCitationAuthor1'
           , 'ContributorCitationAuthor2'];
  }
  /**
    Returns the Contributors that worked on this language.
    If the Contributor is a CitationAuthor, a Year and Pages field may be added.
  */
, getContributors: function(){
    if(this._contributors === null){
      var idFMap = {}; // cId -> field
      _.each(this.getContributorFields(), function(f){
        var cId = this.get(f);
        if(cId && cId !== '')
          idFMap[cId] = f;
      }, this);
      //Finding contributors:
      this._contributors = {}; // Field -> Contributor
      App.contributorCollection.each(function(c){
        var cId = c.get('ContributorIx');
        if(cId in idFMap){
          var field = idFMap[cId];
          if(ms = field.match(/CitationAuthor([12])$/)){
            var n   = ms[1]
              , add = { Year:  this.get('Citation'+n+'Year')
                      , Pages: this.get('Citation'+n+'Pages')};
            c = new Contributor($.extend(c.attributes, add));
          }
          this._contributors[field] = c;
        }
      }, this);
    }
    return this._contributors;
  }
  /**
    Proxy method for TranscriptionMap
  */
, getTranscription: function(word){
    return App.transcriptionMap.getTranscription(this, word);
  }
, isDefaultPhoneticLanguage: function(){
    var isOrt = this.get('IsOrthographyHasNoTranscriptions');
    return parseInt(isOrt) === 0;
  }
});
