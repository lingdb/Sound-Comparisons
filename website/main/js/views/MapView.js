/**
  model: Map
*/
if(typeof(google) != 'undefined'){
  MapView = Backbone.View.extend({
    events: {
      'click #map_menu_viewAll':        'saveSelection'
    , 'click #map_menu_viewLast':       'loadSelection'
    , 'click #map_menu_zoomCenter':     'centerDefault'
    , 'click #map_menu_zoomCoreRegion': 'centerRegion'
    }
  , initialize: function(){
      this.map = new google.maps.Map(
        document.getElementById("map_canvas")
      , this.model.get('mapOptions')
      );
      this.render();
      //SoundControlView:
      this.soundControlView = new SoundControlView({
        el: this.map, model: this});
      //Window resize
      var view = this;
      $(window).resize(function(){view.adjustCanvasSize();});
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
  });
}
