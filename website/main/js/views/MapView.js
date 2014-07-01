/**
  model: Map
*/
MapView = Backbone.View.extend({
  events: {
    'click #map_menu_viewAll':        'saveSelection'
  , 'click #map_menu_viewLast':       'loadSelection'
  , 'click #map_menu_zoomCenter':     'centerDefault'
  , 'click #map_menu_zoomCoreRegion': 'centerRegion'
  }
, initialize: function(){
    this.div = document.getElementById("map_canvas");
    if(this.div === null) return; // Abort
    this.map = new google.maps.Map(this.div, this.model.get('mapOptions'));
    this.render();
    //SoundControlView:
    this.soundControlView = new SoundControlView({
      el: this.map, model: this});
    if(typeof(MouseTrackView) !== 'undefined'){
      this.mouseTrackView = new MouseTrackView({
      el: this.map, model: this});
    }
    //Window resize
    var view = this;
    $(window).resize(function(){view.adjustCanvasSize();});
    google.maps.event.addListener(this.map, 'zoom_changed', function(){
      view.model.placeWordOverlays();
    });
  }
, render: function(){
    this.adjustCanvasSize();
    //Delayed centerRegion:
    var t   = this
      , tid = window.setTimeout(function(){
        t.centerRegion();
        window.clearTimeout(tid);
      }, 1000);
    this.wordOverlayViews = _.map(
      this.model.get('wordOverlays')
    , function(wo){
        return new WordOverlayView({
          el: this.map, model: wo});
      }
    , this
    );
  }
, adjustCanvasSize: function(){
    var canvas = $('#map_canvas')
      , offset = canvas.offset();
    if(canvas.length === 0) return;
    canvas.css('height', window.innerHeight - offset.top - 1 + 'px');
    google.maps.event.trigger(this.map, "resize");
  }
, centerDefault: function(){
    this.map.fitBounds(this.model.get('defaultBounds'));
    $('#map_menu_zoomCenter').addClass('selected');
    $('#map_menu_zoomCoreRegion').removeClass('selected');
  }
, centerRegion: function(){
    this.map.fitBounds(this.model.get('regionBounds'));
    $('#map_menu_zoomCoreRegion').addClass('selected');
    $('#map_menu_zoomCenter').removeClass('selected');
  }
, saveSelection: function(){
    var save = $('div#saveLocation').attr('href');
    localStorage['maps_userSelection'] = save;
  }
, loadSelection: function(e){
    if(localStorage['maps_userSelection']){
      window.location.href = localStorage['maps_userSelection'];
      e.preventDefault();
    }
  }
, fillPSeq: function(direction, playSequence){
    var wos = this.model.sortWordOverlays(direction);
    _.chain(wos).filter(function(wo){
      var view = wo.get('view');
      if(wo.get('added') && view)
        return view.onScreen();
      return false;
    }).each(function(wo){
      playSequence.add(wo.getAudio());
    });
  }
, getBBox: function(){
    //This clever alg. from http://stackoverflow.com/questions/211703/is-it-possible-to-get-the-position-of-div-within-the-browser-viewport-not-withi
    var e = this.div;
    var offset = {x:0,y:0};
    while(e){
      offset.x += e.offsetLeft;
      offset.y += e.offsetTop;
      e = e.offsetParent;
    }
    if(document.documentElement && (document.documentElement.scrollTop || document.documentElement.scrollLeft)){
      offset.x -= document.documentElement.scrollLeft;
      offset.y -= document.documentElement.scrollTop;
    }else if (document.body && (document.body.scrollTop || document.body.scrollLeft)){
      offset.x -= document.body.scrollLeft;
      offset.y -= document.body.scrollTop;
    }else if (window.pageXOffset || window.pageYOffset){
      offset.x -= window.pageXOffset;
      offset.y -= window.pageYOffset;
    }
    e = $(this.div);
    return {
      x1: offset.x
    , y1: offset.y
    , x2: offset.x + e.width()
    , y2: offset.y + e.height()
    , w:  e.width()
    , h:  e.height()
    };
  }
});
