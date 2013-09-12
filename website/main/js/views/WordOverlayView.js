/**
  el:    google.maps.Map
  model: WordOverlay
*/
WordOverlayView = Backbone.View.extend({
  initialize: function(){
    this.model.set({view: this});
    this.model.on('change:edge', this.edgeChanged, this);
    this.setMap(this.el);
  }
// Called when a WordOverlayView is added to a map:
, onAdd: function(){
    //Creating the div:
    var div = document.createElement("div");
    $(div).addClass('mapAudio', 'audio')
          .html(this.model.get('content'))
          .css('background-color', this.model.get('color'));
    //Handling the callbacks:
    $('.transcription', div).each(function(){
      var audio = $(this).next().get(0);
      if(audio){
        $(this).on('click mouseover touchstart', function(e){
          window.App.views.audioLogic.play(audio);
        });
      }
    });
    //Adding the div to the panes:
    var panes = this.getPanes();
    panes.overlayMouseTarget.appendChild(div);
    //Creating the marker:
    var marker = new google.maps.Marker({
      icon: {
        fillColor:    '#000000'
      , fillOpacity:  1
      , path:         google.maps.SymbolPath.CIRCLE
      , scale:        5
      , strokeWeight: 0
      }
    , map:      this.el
    , position: this.model.get('position')
    , title:    this.model.get('hoverText')
    , visible:  true
    });
    //Saving stuff:
    this.model.set({
      div:    div
    , marker: marker
    , added:  true
    });
  }
, getPoint: function(){
    var p = this.model.get('position');
    return this.getProjection().fromLatLngToDivPixel(p);
  }
, draw: function(){
    var bbox = this.model.getBBox()
      , div  = this.model.get('div');
    div.style.left = bbox.x1 + 'px';
    div.style.top  = bbox.y1 + 'px';
  }
, onRemove: function(){
    this.model.get('div').parentNode.removeChild(this.div);
    this.model.get('marker').setMap(null);
    this.model.set({div: null, marker: null});
  }
, onScreen: function(){
    var p = this.model.get('position');
    return this.el.getBounds().contains(p);
  }
, edgeChanged: function(){
    if(this.model.get('added'))
      this.draw();
  }
});
WordOverlayView.prototype = _.extend(
  WordOverlayView.prototype
, google.maps.OverlayView.prototype
);
