/***/
RegionLanguage = Backbone.Model.extend({
  initialize: function(){
    //Field for the memoization of the Language that belongs to this RegionLanguage:
    this._language = null;
  }
  /**
    Returns the Language that belongs to this RegionLanguage.
  */
, getLanguage: function(){
    if(this._language === null){
      var lId = this.get('LanguageIx');
      this._language = App.languageCollection.findWhere({LanguageIx: lId});
      if(this._language){
        this._language._regionLanguage = this;
      }
    }
    return this._language;
  }
  /**
    The RegionId of a RegionLanguage is produced the same way as the Id of a Region:
  */
, getRegionId: Region.prototype.getId
  /**
    RegionLanguageCollection has a custom comparator,
    that we use to keep RegionLanguages in order.
    We want to compare RegionLanguages by RegionGpIx first,
    and by RegionMemberLgIx second.
    To achieve this, sortValues returns an array with both values in it.
  */
, sortValues: function(){
    return [this.get('RegionGpIx'), this.get('RegionMemberLgIx')];
  }
});
