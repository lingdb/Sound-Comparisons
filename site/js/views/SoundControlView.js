/* global document: false */
"use strict";
define(['backbone',
        'jquery',
        'underscore',
        'models/PlaySequence',
        'leaflet'],
       function(Backbone, $, _, PlaySequence, L){
  /**
    el:    google.maps.Map
    model: MapView.model
    SoundControlView will be instantiated by MapView,
    and share it's model and map.
  */
  return Backbone.View.extend({
    initialize: function(){
      this.control = L.control({position: 'topright'});
      this.control.onAdd = function(){
        return $('#map_play_directions').attr('id','SoundControlView').detach().get(0);
      };
    }
  , update: function(){
      //Add control to map if it's not there:
      if(_.isUndefined(this.control._map)){
        this.addControlToMap();
      }
    }
    /**
      This function is called by update
      to add control to the map and bind click handlers.
    */
  , addControlToMap: function(){
      var soundControlView = this;
      //Add control to map:
      this.control.addTo(this.el);
      //Initializing the PlayButtons:
      var buttons = $('#SoundControlView > i').each(function(i, e){
        var target = $(e)
          , direction = target.attr('data-direction')
          , pSeq = new PlaySequence(target);
        // Replace pSeq.showPlay:
        var _showPlay = pSeq.showPlay;
        pSeq.showPlay = function(){
          this.clear();
          _showPlay.call(this);
        };
        // Replace pSeq.showPause:
        var _showPause = pSeq.showPause;
        pSeq.showPause = function(){
          soundControlView.fillPSeq(direction, this);
          //Stop another one running:
          buttons.each(function(){
            var $t = $(this);
            if($t.hasClass('icon-pause')){
              $t.trigger('click');
            }
          });
          //Show this one:
          _showPause.call(this);
        };
      });
    }
    /**
      Fills a PlaySequence with currently displayed entries from the map in the given direction.
      @param direction 'ns'|'sn'|'we'|'ew'
      @param playSequence PlaySequence
      Strategy:
      - Filter all transcriptions that are in current bounds.
      - Project transcriptions to points.
      - Sort points by direction.
      - Add audio to playSequence for given points sequence.
    */
  , fillPSeq: function(direction, playSequence){
      var bnds = this.el.getBounds()
        , ts = _.chain(this.model.mapsData.transcriptions)
                .filter(function(t){return bnds.contains(t.latlng);})
                .map(function(t){
                  var p = this.el.latLngToLayerPoint(t.latlng);
                  return {
                    transcription: t
                  , val: (direction === 'ns' || direction === 'sn') ? p.y : p.x
                  };
                }, this)
                .sortBy(function(x){return x.val;})
                .map(function(x){return x.transcription;})
                .value();
      if(direction === 'sn' || direction === 'ew'){
        ts.reverse();
      }
      _.each(ts, function(t){
        _.each(t.getAudio(), function(a){
          playSequence.add(a);
        }, this);
      }, this);
    }
  });
});
