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
        ixM = this.get('IxMorphologicalInstance');
    return ''+ixE+ixM;
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
      , fallback = this.get('LongerRfcModernLg01')
      , fallbac_ = this.get('LongerRfcModernLg02');
    if(typeof(fallback) !== 'string' || fallback === '')
      fallback = fallbac_;
    if(fallback === '')
      fallback = null;
    return App.translationStorage.translateDynamic(category, this.getField(), fallback);
  }
  /**
    Produces the ProtoName for the current Word.
  */
, getProtoName: function(){
    return this.get('FullRfcProtoLg01');
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
//FIXME implement fetching of Neighbours
//FIXME implement language dependant translation
});
