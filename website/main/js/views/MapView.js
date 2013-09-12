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
    this.map = new google.maps.Map(
      document.getElementById("map_canvas")
    , this.model.get('mapOptions')
    );
    this.render();
    //The play buttons:
    var view = this;
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
        view.fillPSeq(direction, this);
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
, render: function(){
    //The canvas needs more size:
    $('#map_canvas').css('height', window.innerHeight * .75 + 'px');
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
