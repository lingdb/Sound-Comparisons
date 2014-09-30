"use strict";
if(typeof(google) !== 'undefined'){
  var Map = Backbone.Model.extend({
    defaults: {
      mapOptions: {
        zoom:              2
      , center:            new google.maps.LatLng(54.92, 1.875)
      , mapTypeId:         google.maps.MapTypeId.TERRAIN
      , streetViewControl: false
      , styles:            [{
        featureType: "administrative.country"
        , elementType: "labels"
        , stylers: [{visibility: "off"}]
        }]
      }
    , defaultBounds: new google.maps.LatLngBounds()
    , regionBounds:  new google.maps.LatLngBounds()
    , wordOverlays:  []
    , notAddedWos:   0
    }
    /**
      Called by views/render/MapView to set a model to represent on the map.
    */
  , setModel: function(m){
      //Removing old wordOverlays:
      _.each(this.get('wordOverlays'), function(wo){
        var v = wo.get('view');
        if(v) v.remove();
      }, this);
      //Setting new data:
      this.set(m);
      this.initRegionBounds();
      //Building WordOverlays:
      var map = App.views.renderer.model.mapView.map;
      var wos = _.map(
        this.get('transcriptions')
      , function(t){
          var wo = new WordOverlay(t);
          wo.on('change:added', this.wordOverlayAdded, this);
          //WordOverlayView will be added as the view to the WordOverlay.
          wo.set({view: new WordOverlayView({model: wo, el: map})});
          return wo;
        }
      , this
      );
      this.set({
        wordOverlays: wos
      , notAddedWos:  wos.length
      });
    }
  , initRegionBounds: function(){
      var rBounds = this.get('regionBounds');
      _.each(this.get('regionZoom'), function(e){
        var latLng = new google.maps.LatLng(e.lat, e.lon);
        rBounds.extend(latLng);
      }, this);
    }
  , sortWordOverlays: function(direction){
      if(!/^(ns|sn|we|ew)$/i.test(direction)){
        console.log('Map.sortWordOverlays('+direction+'): Invalid direction!');
        return; // Exit if direction is invalid
      }
      //The list that we use to sort:
      var sortList = _.map(
        this.get('wordOverlays')
      , function(wo){
          var p = wo.get('position');
          return [p.lat(), p.lng(), wo];
        }
      );
      //Should we sort by lon first?
      if(/^(we|ew)$/i.test(direction)){
        sortList = _.map(sortList, function(e){
          return [e[1],e[0],e[2]];
        });
      }
      //The sorting itself:
      sortList.sort(function(a, b){
        return a[0] - b[0];
      });
      //Should we reverse the list?
      if(/^(ns|ew)$/i.test(direction))
        sortList.reverse();
      //Done:
      return _.map(sortList, _.last);
    }
  , wordOverlayAdded: function(wo){
      if(!wo.get('added')) return;
      wo.off(null, null, this);
      var c = this.get('notAddedWos') - 1;
      this.set({notAddedWos: c});
      if(c === 0){
        var t = this;
        var tid = window.setTimeout(function(){
          window.clearTimeout(tid);
          t.placeWordOverlays();
        },1000);
      }
      this.get('defaultBounds').extend(wo.get('position'));
    }
  , placeWordOverlays: function(){
      /**
        The placement of WordOverlays (their edges) works in the following fashion:
        - WordOverlays have a priority order for their edges,
          from the edges they 'like' best,
          to the ones they 'like' least.
          This order is ['sw','se','nw','ne'].
        - WordOverlays are progressed from north to south.
        - The first WordOverlay keeps it's edge
        - The second WordOverlay chooses it's edge,
          so that it doesn't overlap with the first.
        - All other WordOverlays choose their edges only with respect
          to the preceding two WordOverlays.
      */
      var wos = this.sortWordOverlays('ns');
      _.reduce(wos, function(prec, wo){
        wo.place(_.last(prec, 2));
        prec.push(wo);
        return prec;
      }, [], this);
    }
  });
}
