/* global document: false, google: false */
"use strict";
define(['jquery','underscore','backbone'], function($, _, Backbone){
  /**
    el:  google.maps.Map
    model: MapView
  */
  return Backbone.View.extend({
    initialize: function(){
      if(!_.isUndefined(window.google)){
        var view = this;
        google.maps.event.addListener(this.el, 'click', function(e){
          view.mouseAtLatLng(e.latLng);
        });
        this.div = $(document.createElement('div'));
        this.el.controls[google.maps.ControlPosition.BOTTOM_LEFT].push(this.div.get(0));
      }
    }
  , mouseAtLatLng: function(ll){
      this.div.html('Mouse at '+ll.lat().toFixed(3)+' : '+ll.lng().toFixed(3));
    }
  });
});
