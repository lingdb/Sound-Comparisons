/* global Colors: true */
"use strict";
/**
  Colors is basically a pot of colors to be used for coloring of families and languages.
  Depending on an index they can fetch colors.
*/
var Colors = Backbone.Model.extend({
  defaults: {
    colors: [
      '#E6E6E6'
    , '#CCCCFF'
    , '#CCFFFF'
    , '#F2F6CA'
    , '#FFFACD'
    , '#FFCC99'
    , '#C59595'
    , '#C0C0C0'
    , '#FFFFFF'
    ]
  }
, getColor: function(i){
    var cs = this.get('colors');
    return cs[i % cs.length];
  }
});
