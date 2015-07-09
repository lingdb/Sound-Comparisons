"use strict";
define(['backbone'], function(Backbone){
  /**
    TranscriptionSuperscript encapsules both, transcrSuperscriptInfo and transcrSuperscriptLenderLgs.
  */
  return Backbone.Model.extend({
    /**
      Helper for is{LenderLgs,SuperscriptInfo}
    */
    hasKeys: function(keys){
      for(var i = 0; i < keys.length; i++){
        if(!(keys[i] in this.attributes))
          return false;
      }
      return true;
    }
    /**
      True iff instance belongs to the transcrSuperscriptInfo.
    */
  , isLenderLgs: function(){
      var expected = ['Abbreviation','FullNameForHoverText','IsoCode'];
      return this.hasKeys(expected);
    }
    /**
      True iff instance belongs to the transcrSuperscriptLenderLgs.
    */
  , isSuperscriptInfo: function(){
      var expected = ['Abbreviation','HoverText','Ix'];
      return this.hasKeys(expected);
    }
  });
});
