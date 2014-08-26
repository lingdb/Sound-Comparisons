/***/
RegionLanguage = Backbone.Model.extend({
  /**
    The RegionId of a RegionLanguage is produced the same way as the Id of a Region:
  */
  getRegionId: Region.prototype.getId
});
