/* global document: false */
"use strict";
define(['backbone','models/PlaySequence'], function(Backbone, PlaySequence){
  /**
    el:    google.maps.Map
    model: MapView
  */
  return Backbone.View.extend({
    initialize: function(){
    }
  , update: function(){
      if($('#map_play_directions').length === 0) return;
      if($('#SoundControlView').length > 0) return;
      var div     = document.createElement('div')
        , dirs    = $('#map_play_directions')
        , mapView = this.model;
      $(div).attr('id','SoundControlView').html(dirs.html()).find('i').each(function(i, e){
        //Initializing the PlayButtons…
        var target    = $(e);
        var direction = target.attr('data-direction');
        var pSeq      = new PlaySequence(target);
        var _showPlay = pSeq.showPlay;
        pSeq.showPlay = function(){
          this.clear();
          _showPlay.call(this);
        };
        var _showPause = pSeq.showPause;
        pSeq.showPause = function(){
          mapView.fillPSeq(direction, this);
          //Stop another one running:
          $('#SoundControlView > i').each(function(){
            if($(this).hasClass('icon-pause'))
              $(this).trigger('click');
          });
          //Show this one:
          _showPause.call(this);
        };
      });
      //Placing the control on the Map:
      this.el.controls[google.maps.ControlPosition.TOP_LEFT].push(div);
      //A little cleanup:
      dirs.remove();
    }
  });
});
