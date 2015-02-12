/* global LanguageStatusType: true */
"use strict";
/***/
var LanguageStatusType = Backbone.Model.extend({
  /**
    Helper method to construct the Category used for dynamic translation.
  */
  getCategory: function(suffix){
    return 'LanguageStatusTypesTranslationProvider-LanguageStatusTypes-Trans_'+suffix;
  }
  /**
    Helper method to return the field used for dynamic translation.
  */
, getField: function(){
    return this.get('LanguageStatusType');
  }
  /**
    Returns the status field for a LanguageStatusType in the current translation.
  */
, getStatus: function(){
    var category = this.getCategory('Status')
      , fallback = this.get('Status');
    return App.translationStorage.translateDynamic(category, this.getField(), fallback);
  }
  /**
    Returns the StatusTooltip field for a LanguageStatusType in the current translation.
  */
, getStatusTooltip: function(){
    var category = this.getCategory('StatusTooltip')
      , fallback = this.get('StatusTooltip');
    return App.translationStorage.translateDynamic(category, this.getField(), fallback);
  }
  /**
    Returns the Description for a LanguageStatusType in the current translation.
  */
, getDescription: function(){
    var category = this.getCategory('Description')
      , fallback = this.get('Description');
    return App.translationStorage.translateDynamic(category, this.getField(), fallback);
  }
});
