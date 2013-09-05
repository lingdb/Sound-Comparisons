/***/
function Map(){
  var t = this;
  //Zoom and center of the map:
  var wV = {zoom: 2, lat:54.92, lng: 1.875};
  //Options to init the map with:
  this.myOptions = {
      zoom:               wV.zoom
    , center:             new google.maps.LatLng(wV.lat, wV.lng)
    , mapTypeId:          google.maps.MapTypeId.TERRAIN
    , streetViewControl:  false
    , styles:             [{
      featureType: "administrative.country"
      , elementType: "labels"
      , stylers: [{visibility: "off"}]
      }]
  };
  //The map:
  this.map = new google.maps.Map(
               document.getElementById("map_canvas")
             , this.myOptions);
  //MapData:
  this.mapData = $.parseJSON($('div#map_data').text());
  //Bounds to center the map:
  this.defaultBounds = new google.maps.LatLngBounds();
  this.regionBounds  = new google.maps.LatLngBounds();
  //Building the regionBounds:
  $(this.mapData.regionZoom).each(function(i, e){
    var latLng = new google.maps.LatLng(e.lat, e.lon);
    t.regionBounds.extend(latLng);
  });
  /*
    WordOverlays:
    To keep track if all WordOverlays are ready,
    we use the number in woGuard, and decrement it for every wo
    that calls wordOverlayAdded, so that woGuard===0 when all are done.
  */
  this.woGuard = this.mapData.transcriptions.length;
  this.woList  = [];
  //Loading the transcriptions:
  $(this.mapData.transcriptions).each(function(i, d){
    t.woList.push(t.addTranscription(d));
  });
  var woList = this.woList;
  $(woList).each(function(i, wo){
    wo.setEdge(woList);
  });
}
/***/
Map.prototype.addTranscription = function(t){
  var content  = this.mkContent(t);
  var position = new google.maps.LatLng(t.lat, t.lon);
  this.defaultBounds.extend(position); // Extending the bounds
  return new WordOverlay(this.map, position, content, t.color, this, t.langName);
};
/***/
Map.prototype.mkContent = function(d){
  var content = '';
  //Parsing the phoneticSoundfiles:
  $(d.phoneticSoundfiles).each(function(j, sf){
    //Building audioelements:
    var audio = "";
    if(sf.soundfiles.length > 0)
      audio = "<audio data-onDemand='"
            + JSON.stringify(sf.soundfiles)
            + "' autobuffer='' preload='auto'></audio>";
    var fileMissing = ''; //Historical entries -> no files
    if(d.historical == 1 || audio === "")
      fileMissing = ' fileMissing';
    if(d.historical == 1)
      sf.phonetic = "*" + sf.phonetic;
    if(content !== "")
      content += ",";
    content += "<div class='transcription"
             + fileMissing
             + "'>" + sf.phonetic + "</div>"
             + audio;
  });
  return content;
};
/***/
Map.prototype.centerDefault = function(){
  this.map.fitBounds(this.defaultBounds);
  $('#map_menu_zoomCenter').addClass('selected');
  $('#map_menu_zoomCoreRegion').removeClass('selected');
};
/***/
Map.prototype.centerRegion = function(){
  this.map.fitBounds(this.regionBounds);
  $('#map_menu_zoomCoreRegion').addClass('selected');
  $('#map_menu_zoomCenter').removeClass('selected');
};
/***/
Map.prototype.saveSelection = function(){
  var save = $('div#saveLocation').attr('href');
  $.cookie("maps_userSelection", save);
};
/***/
Map.prototype.loadSelection = function(e){
  //Detecting if a last selection is known:
  if($.cookie("maps_userSelection")){
    //Jumping to last selection:
    window.location.href = $.cookie("maps_userSelection");
    e.preventDefault();
  }
};
/**
  @param wo WordOverlay that is created by this Map
  When onAdd is called on a WordOverlay, it notifies it's pMap (this Map)
  of that, so that it can be added to PlaySequences.
*/
Map.prototype.wordOverlayAdded = function(wo){
  this.woGuard--;
};
/**
  @param direction String expected to be any of ["ns","sn","we","ew"].
  Plays all currently displayed wo in the requested direction on the map.
*/
Map.prototype.fillPSeq = function(direction, ps){
  if(this.woGuard !== 0){
    console.log('Map.playSequence called before woList was ready.');
    return; // Exit if not all wo are in woList
  }
  if(!/^(ns|sn|we|ew)$/i.test(direction)){
    console.log('Wrong direction in Map.playSequence:\t' + direction);
    return; // Exit if direction is invalid
  }
  //woList only to contain wo that are on screen:
  var woList = $(this.woList).filter(function(i){
    return this.onScreen();
  });
  //The list to sort the audio elements by:
  var sortList = [];
  $(woList).each(function(i, wo){
    sortList.push([ wo.position.lat()
                  , wo.position.lng()
                  , wo.getAudio()]);
  });
  //Should we sort by lon first?
  if(/^(we|ew)$/i.test(direction)){
    flippedSortList = [];
    $(sortList).each(function(i, e){
      flippedSortList.push([e[1],e[0],e[2]]);
    });
    sortList = flippedSortList;
  }
  //The sorting itself:
  sortList.sort(function(a, b){
    return a[0] - b[0];
  });
  //Should we reverse the list?
  if(/^(ns|ew)$/i.test(direction))
    sortList.reverse();
  //Building the PlaySequence:
  $(sortList).each(function(i, e){
    console.log('Adding to PlaySequence:\t' + e);
    console.log(e[2]);
    ps.add(e[2]);
  });
  return ps;
};
/***/
function initMaps(){
  //Should we init?
  if($('#map_canvas').length < 1)
    return;
  //THe canvas needs more size:
  $('#map_canvas').css('height', window.innerHeight * .75 + 'px');
  //Our map:
  var map = new Map();
  //Switching selection to all:
  $('#map_menu_viewAll').click(function(){
    map.saveSelection();
  });
  //Switching to last selection:
  $('#map_menu_viewLast').click(function(){
    map.loadSelection();
  });
  //Centering the map on default Bounds:
  $('#map_menu_zoomCenter').click(function(){
    map.centerDefault();
  });
  //Centering the map on region Bounds:
  $('#map_menu_zoomCoreRegion').click(function(){
    map.centerRegion();
  });
  setTimeout(function(){map.centerRegion();}, 1000);
  //The play buttons:
  $('#map_play_directions > i').each(function(i, e){
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
      map.fillPSeq(direction, this);
      //Stop another one running:
      $('#map_play_directions > i').each(function(){
        if($(this).hasClass('icon-pause'))
          $(this).trigger('click');
      });
      //Show this one:
      _showPause.call(this);
    };
  });
}
