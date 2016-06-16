/* global document: false */
"use strict";
define(['backbone',
        'jquery',
        'models/PlaySequence',
        'leaflet'],
       function(Backbone, $, PlaySequence, L){
  /**
    el:    google.maps.Map
    model: MapView.model
    SoundControlView will be instantiated by MapView,
    and share it's model and map.
  */
  return Backbone.View.extend({
    initialize: function(){
      this.control = L.control({position: 'topright'});
      this.control.onAdd = function(map){
        return $('#map_play_directions').attr('id','SoundControlView').detach().get(0);
      };
      //control.addTo(this.el);
    }
  , update: function(){
      //Add control to map if it's not there:
      if(_.isUndefined(this.control._map)){
        this.control.addTo(this.el);
      }
      //Make sure we show what
  //  if($('#map_play_directions').length === 0) return;
  //  if($('#SoundControlView').length > 0) return;
  //  var div     = document.createElement('div')
  //    , dirs    = $('#map_play_directions')
  //    , mapView = this.model;
  //  $(div).attr('id','SoundControlView').html(dirs.html()).find('i').each(function(i, e){
  //    //Initializing the PlayButtons…
  //    var target    = $(e);
  //    var direction = target.attr('data-direction');
  //    var pSeq      = new PlaySequence(target);
  //    var _showPlay = pSeq.showPlay;
  //    pSeq.showPlay = function(){
  //      this.clear();
  //      _showPlay.call(this);
  //    };
  //    var _showPause = pSeq.showPause;
  //    pSeq.showPause = function(){
  //      mapView.fillPSeq(direction, this);
  //      //Stop another one running:
  //      $('#SoundControlView > i').each(function(){
  //        if($(this).hasClass('icon-pause'))
  //          $(this).trigger('click');
  //      });
  //      //Show this one:
  //      _showPause.call(this);
  //    };
  //  });
  //  //Placing the control on the Map:
  //  this.el.controls[google.maps.ControlPosition.TOP_LEFT].push(div);
  //  //A little cleanup:
  //  dirs.remove();
    }
  });
});
