/* global MouseTrackView: true, document: false */
"use strict";
/**
  el:  google.maps.Map
  model: MapView
*/
var MouseTrackView = Backbone.View.extend({
  initialize: function(){
    if(typeof(google) !== 'undefined'){
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
