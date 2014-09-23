/***/
Word = Backbone.Model.extend({
  initialize: function(){
    //Field for memoization of the MeaningGroup this Word belongs to.
    this._meaningGroup = null;
  }
  /**
    Returns the Id for a Word, which is the concatenation of IxElicitation and IxMorphologicalInstance.
  */
, getId: function(){
    var ixE = this.get('IxElicitation')
      , ixM = this.get('IxMorphologicalInstance');
    return ''+ixE+ixM;
  }
  /**
    Returns the Key for a Word.
    In contrast to the Id, the key is human readable, and may have duplicates.
    I will potentially add the getKey method to many models that already have a getId.
  */
, getKey: function(){
    return this.get('FullRfcModernLg01');
  }
  /**
    Helper method to produce the category necessary to fetch the dynamic translation.
  */
, getCategory: function(suffix){
    return 'WordsTranslationProvider-Words_-Trans_'+suffix;
  }
  /**
    Helper method to produce the field necessary to fetch the dynamic translation.
  */
, getField: function(){
    return ''+App.study.get('Name')+'-'+this.getId();
  }
  /**
    Produces the ModernName for the current Word in the current translation.
  */
, getModernName: function(){
    var category = this.getCategory('FullRfcModernLg01')
      , fallback = this.get('FullRfcModernLg01')
      , fallbac_ = this.get('FullRfcModernLg02');
    if(typeof(fallback) !== 'string' || fallback === '')
      fallback = fallbac_;
    return App.translationStorage.translateDynamic(category, this.getField(), fallback);
  }
  /**
    Produces the LongName for the current Word in the current translation.
  */
, getLongName: function(){
    var category = this.getCategory('LongerRfcModernLg01')
      , fallback = this.get('LongerRfcModernLg01');
    if(!_.isString(fallback) || _.isEmpty(fallback))
      fallback = this.get('LongerRfcModernLg02');
    if(_.isEmpty(fallback))
      fallback = null;
    return App.translationStorage.translateDynamic(category, this.getField(), fallback);
  }
  /**
    Produces the ProtoName for the current Word.
  */
, getProtoName: function(){
    return this.get('FullRfcProtoLg01');
  }
  /***/
, getNameFor: function(language){
    if(language){
      var t    = App.transcriptionMap.getTranscription(language, this)
        , alts = t.getSpellingAltv();
      if(alts.length > 0)
        return alts;
    }
    return this.getModernName();
  }
  /**
    Returns the MeaningGroup that this word belongs in.
  */
, getMeaningGroup: function(){
    if(this._meaningGroup === null){
      var query = {MeaningGroupIx: this.get('MeaningGroupIx')};
      this._meaningGroup = App.meaningGroupCollection.findWhere(query);
    }
    return this._meaningGroup;
  }
  /**
    Proxy method for TranscriptionMap
  */
, getTranscription: function(language){
    return App.transcriptionMap.getTranscription(language, this);
  }
  /**
    getNeighbour shall usually be called via get{Prev,Next}.
    It returns the next or previous Word with respect to the current wordOrder.
    Since the App.wordCollection is always kept in the correct order depending on App.pageState,
    we can just select the correct one of it's models.
  */
, getNeighbour: function(next){
    var key   = next ? 1 : -1 // Key already contains direction.
      , words = App.wordCollection.models
      , wId   = this.getId();
    //Find current position in words array and add it to the key:
    for(var i = 0; i < words.length; i++){
      if(words[i].getId() === wId){
        key += i;
        break;
      }
    }
    //Wrapping the key around the words:
    key %= words.length;
    if(key < 0) key += words.length;
    //Done:
    return words[key];
  }
, getPrev: function(){return this.getNeighbour(false);}
, getNext: function(){return this.getNeighbour(true);}
});
