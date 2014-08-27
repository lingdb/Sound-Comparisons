/***/
RegionLanguage = Backbone.Model.extend({
  /**
    The RegionId of a RegionLanguage is produced the same way as the Id of a Region:
  */
  getRegionId: Region.prototype.getId
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
