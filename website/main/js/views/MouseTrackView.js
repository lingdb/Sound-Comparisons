/**
  el:  google.maps.Map
  model: MapView
*/
MouseTrackView = Backbone.View.extend({
  initialize: function(){
    var view = this;
    google.maps.event.addListener(this.el, 'click', function(e){
      view.mouseAtLatLng(e.latLng);
    });
    this.div = $(document.createElement('div'));
    this.el.controls[google.maps.ControlPosition.BOTTOM_LEFT].push(this.div.get(0));
  }
, mouseAtLatLng: function(ll){
  //var bnds = this.el.getBounds()
  //  , ne   = bnds.getNorthEast()
  //  , sw   = bnds.getSouthWest();
  //this.div.html(JSON.stringify(this.el.getBounds()));
    this.div.html('Mouse at '+ll.lat().toFixed(3)+' : '+ll.lng().toFixed(3));
  }
});
