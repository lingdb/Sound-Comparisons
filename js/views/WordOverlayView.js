"use strict";
/**
  Our constructor expects the following options:
  el:    google.maps.Map
  model: WordOverlay
*/
var WordOverlayView = function(o){
  //Required properties of google.maps.OverlayView:
  this.bounds_ = null;
  this.image_  = null;
  this.map_    = o.el;
  //Custom properties:
  this.model = o.model;
  //Custom methods:
  //Called when a WordOverlayView is added to a map:
  this.onAdd = function(){
    //Creating the div:
    var div   = document.createElement("div")
      , color = this.model.get('color');
    //Calculating the color values:
    color.opacity /= 100;
    //Filling and styling the div:
    $(div).addClass('mapAudio', 'audio')
          .html(this.model.get('content'))
          .css('background-color', color.color)
          .find('.transcription').css('font-size','14px');
    //Handling the callbacks:
    $('.transcription', div).each(function(){
      var audio = $(this).next().get(0);
      if(audio){
        $(this).on('click mouseover touchstart', function(e){
          if(e.type === 'mouseover')
            if(!App.soundPlayOption.playOnHover())
              return;
          window.App.views.audioLogic.play(audio);
        });
      }
    });
    //Adding the div to the panes:
    var panes = this.getPanes();
    panes.overlayMouseTarget.appendChild(div);
    //Creating the marker:
    var mOpts = {
      icon: {
        fillColor:    '#000000'
      , fillOpacity:  1
      , path:         google.maps.SymbolPath.CIRCLE
      , scale:        5
      , strokeWeight: 0
      }
    , map:      this.map_
    , position: this.model.get('position')
    , title:    this.model.get('hoverText')
    , visible:  true
    };
    var marker = new google.maps.Marker(mOpts);
    //Saving stuff:
    this.model.set({
      div:    div
    , marker: marker
    , added:  true
    });
  };
  /**
    The problem with getPoint is that it depends on the map being 'ready'.
    To achive this, we wait for the first idle event in case getProjection is empty.
    Because of this, getPoint can only return a Promise for a Point, but not a Point itself.
  */
  this.getPoint = function(){
    var pos  = this.model.get('position')
      , prom = $.Deferred()
      , prj  = this.getProjection();
    if(_.isEmpty(prj)){
      var t = this;
      google.maps.event.addListenerOnce(this.map_, 'idle', function(){
        prom.resolve(t.getProjection().fromLatLngToDivPixel(pos));
      });
    }else{
      prom.resolve(prj.fromLatLngToDivPixel(pos));
    }
    return prom;
  };
  //The draw method:
  this.draw = function(){
    var div = this.model.get('div');
    this.model.getBBox().done(function(bbox){
      div.style.left = bbox.x1 + 'px';
      div.style.top  = bbox.y1 + 'px';
    });
  }
  //Making removal possible:
  this.remove = function(){
    this.setMap(null);
  };
  //Handling removal from the map:
  this.onRemove = function(){
    var div = this.model.get('div')
    if(div instanceof Node){
      div.parentNode.removeChild(div);
    };
    this.model.get('marker').setMap(null);
    this.model.set({div: null, marker: null});
  }
  //Predicate to chk iff this overlay is on the screen.
  this.onScreen = function(){
    var p = this.model.get('position');
    return this.map_.getBounds().contains(p);
  }
  //Callback to handle changed edges
  this.edgeChanged = function(){
    if(this.model.get('added'))
      this.draw();
  }
  //Callbacks:
  this.model.on('change:edge', this.edgeChanged, this);
  //Setting the map:
  this.setMap(o.el);
};
/**
  Because of reasons, I have problems with WordOverlayView being an instance of both,
  ColerCalcView and google.maps.OverlayView.
  To work around this, I reimplemented WordOverlayView in a fashion that makes it independant of ColorCalcView below.
*/
if(typeof(google) !== 'undefined'){
  WordOverlayView.prototype = new google.maps.OverlayView();
}
