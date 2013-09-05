/*
  @param map google.maps.Map
  @param position google.maps.LatLng
  @param content String html to add inside a div[class="mapAudio audio"]
  @param color String of format '#xxxxxx' with x being any hex value.
  @param pMap Map from maps.js to call back on onAdd.
  @param hoverText String to be displayed on hovering the Marker.
  The WordOverlay is a child of google.maps.OverlayView, and is implemented close to
  'https://developers.google.com/maps/documentation/javascript/overlays'.
  The WordOverlay is created on a map given a position.
  It displays the given content inside a div with the classes mapAudio, audio.
  The WordOverlay searches the content for div for div.transcription
  and will add hover effects to these playing the following audio tag.
*/
function WordOverlay(map, position, content, color, pMap, hoverText){
  //Checking for color option to be valid.
  if(!/^#[0123456789abcdef]{6}$/i.test(color)){
    color = '#000000';
  }
  //Initializing variables:
  this.id        = WordOverlay.prototype.idCounter++;
  this.map       = map;       // The map on which to display
  this.position  = position;  // Where on the map to display
  this.content   = content;   // Content to display
  this.color     = color;     // The background color of the div
  this.pMap      = pMap;      // Map from maps.js to notify of add to the google.maps.Map
  this.hoverText = hoverText; // Displayed when hovering the marker.
  this.div       = null;      // The div element that will be used to render our content
  this.marker    = null;      // The 'blob' on the lower left edge of the div
  this.edge      = 'sw';      // Edge on which the 'blob' will be displayed, fits the regex /^[ns][we]$/
  //Setting the map:
  this.setMap(map);
}
//WordOverlay inherits OverlayView:
WordOverlay.prototype = new google.maps.OverlayView();
//What happens when a WordOverlay is added to a map:
WordOverlay.prototype.onAdd = function(){
  //Creating the div:
  var div = document.createElement("div");
  $(div).addClass('mapAudio', 'audio')
        .html(this.content)
        .css('background-color', this.color);
  this.div = div; // Saving the div.
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
  this.marker = new google.maps.Marker({
    icon: {
      fillColor:    '#000000'
    , fillOpacity:  1
    , path:         google.maps.SymbolPath.CIRCLE
    , scale:        5
    , strokeWeight: 0
    }
  , map:      this.map
  , position: this.position
  , title:    this.hoverText
  , visible:  true
  });
  //Notifying the pMap:
  if(this.pMap)
    this.pMap.wordOverlayAdded(this);
};
/***/
WordOverlay.prototype.getPoint = function(){
  return this.getProjection().fromLatLngToDivPixel(this.position);
}
//How to draw our WordOverlay:
WordOverlay.prototype.draw = function(){
  //Fetching elements to work with:
  var p    = this.getPoint();
  var div  = this.div;
  var edge = this.edge;
  //Modifying position to fit the edge:
  if(/^nw$/i.test(edge)){}
  if(/^ne$/i.test(edge)){p.x = p.x - $(div).width();}
  if(/^sw$/i.test(edge)){p.y = p.y - $(div).height();}
  if(/^se$/i.test(edge)){
    p.y = p.y - $(div).height();
    p.x = p.x - $(div).width();
  }
  //Updating position:
  div.style.left = p.x + 'px';
  div.style.top  = p.y + 'px';
};
//How to remove a WordOverlay:
WordOverlay.prototype.onRemove = function(){
  this.div.parentNode.removeChild(this.div);
  this.marker.setMap(null);
  this.div    = null;
  this.marker = null;
};
//Hiding the WordOverlay:
WordOverlay.prototype.hide = function(){
  $(this.div).hide();
  this.marker.setVisible(false);
};
//Displaying the WordOverlay:
WordOverlay.prototype.show = function(){
  $(this.div).show();
  this.marker.setVisible(true);
};
//Toggle the visibility of the WordOverlay:
WordOverlay.prototype.toggle = function(){
  $(this.div).toggle();
  this.marker.setVisible(!this.marker.getVisible());
};
/***/
WordOverlay.prototype.getAudio = function(){
  if(!this.div) return null;
  return $('audio', this.div).get(0);
};
/**
  @return onScreen bool
  Tells if the WordOverlay is currently visible to the user.
*/
WordOverlay.prototype.onScreen = function(){
  return this.map.getBounds().contains(this.position);
};
/***/
WordOverlay.prototype.getDistance = function(wo){
  var p1 = this.position;
  var p2 = wo.position;
  return google.maps.geometry.spherical.computeDistanceBetween(p1, p2);
};
/***/
WordOverlay.prototype.idCounter = 0;
/***/
WordOverlay.prototype.equals = function(wo){
  return wo.id === this.id; 
};
/***/
WordOverlay.prototype.getEdgeForPosition = function(p_){
  var p = this.position;
  var d = {x: p_.lng() - p.lng(), y: p_.lat() - p.lat()};
  var e = this.edge; //To keep the default if no clear decision is made.
  if(d.x > 0 && d.y > 0){ e = 'ne'; }
  if(d.x > 0 && d.y < 0){ e = 'se'; }
  if(d.x < 0 && d.y > 0){ e = 'nw'; }
  if(d.x < 0 && d.y < 0){ e = 'sw'; }
  return e;
};
/***/
WordOverlay.prototype.setEdge = function(wos){
  var t = this;
  var edges = $.map(wos, function(wo){
    if(t.equals(wo)) return null;
    return {
      distance: wo.getDistance(t)
    , edge:     wo.edge
    , position: wo.position};
  });
  edges.sort(function(x, y){
    return x.distance - y.distance;
  });
  if(edges.length){
    var e = edges[0];
    t.edge = t.getEdgeForPosition(e.position);
    //Handling collisions:
    if(e.position.equals(t.position))
      if(t.edge === e.edge){
        if(e.edge === "sw"){ t.edge = 'nw'; }
        if(e.edge === "nw"){ t.edge = 'ne'; }
        if(e.edge === "ne"){ t.edge = 'se'; }
        if(e.edge === "se"){ t.edge = 'sw'; }
      }
  }
};
