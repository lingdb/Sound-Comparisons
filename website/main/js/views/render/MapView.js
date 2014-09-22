/***/
MapView = Renderer.prototype.SubView.extend({
  /***/
  initialize: function(){
    //Data representation created by update methods:
    this.model = {}; // Notice that we also make heavy use of App.map
    //Map setup:
    this.div = document.getElementById("map_canvas");
    this.map = new google.maps.Map(this.div, App.map.get('mapOptions'));
    this.renderMap();
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
      App.map.placeWordOverlays();
    });
    //Connecting to the router
    App.router.on('route:mapView', this.route, this);
  }
  /**
    Method to make it possible to check what kind of PageView this Backbone.View is.
  */
, getKey: function(){return 'map';}
  /***/
, activate: function(){
    //Setting callbacks to update model:
    App.translationStorage.on('change:translationId', this.buildStatic, this);
    //Building statics the first time:
    this.buildStatic();
  }
  /***/
, buildStatic: function(){
    _.extend(this.model, App.translationStorage.translateStatic({
      mapsMenuToggleShow: 'maps_menu_toggleShow'
    , mapsMenuViewAll:    'maps_menu_viewAll'
    , mapsMenuViewLast:   'maps_menu_viewLast'
    , mapsMenuCenterMap:  'maps_menu_centerMap'
    , mapsMenuCoreRegion: 'maps_menu_viewCoreRegion'
    , mapsMenuPlayNs:     'maps_menu_playNs'
    , mapsMenuPlaySn:     'maps_menu_playSn'
    , mapsMenuPlayWe:     'maps_menu_playWe'
    , mapsMenuPlayEw:     'maps_menu_playEw'
    }));
  }
  /**
    We need the same WordHeadline as WordView, so we reuse it for MapView.
    Sadly we still need a proxy function, at least to make sure WordView is already defined.
  */
, updateWordHeadline: function(){
    return WordView.prototype.updateWordHeadline.call(this, arguments);
  }
  /**
    TODO viewLast currently has the defaults, which is probably not what we want.
         In that case we'll have to introduce Selection:getPreviousSelection or something.
  */
, updateLinks: function(){
    var lc = App.languageCollection;
    _.extend(this.model, {
      viewAll:     'href="'+App.router.linkMapView({languages: lc})+'"'
    , viewLast:    'href="'+App.router.linkMapView({languages: App.defaults.getLanguages()})+'"'
    , allSelected: lc.length === lc.getSelected().length
    });
  }
  /***/
, updateMapsData: function(){
    var data = {
      transcriptions: []
    , regionZoom: App.study.getMapZoomCorners()
    }, word = App.wordCollection.getChoice();
    //Iterating languages:
    _.each(App.languageCollection.getSelected(), function(l){
      var latlon = l.getLocation();
      if(latlon === null) return;
      var tr = App.transcriptionMap.getTranscription(l, word);
      //Creating psf entries:
      var psf = _.map(tr.getPhonetics(), function(p){
        return { phonetic:   p.phonetic
               , soundfiles: p._srcs };
      }, this);
      //The complete structure:
      data.transcriptions.push({
        altSpelling:        tr.getAltSpelling()
      , translation:        word.getNameFor(l)
      , lat:                latlon[0]
      , lon:                latlon[1]
      , historical:         l.isHistorical() ? 1 : 0
      , phoneticSoundfiles: psf
      , langName:           l.getShortName()
      , languageLink:       'href="'+App.router.linkLanguageView({language: l})+'"'
      , familyIx:           l.getFamilyIx()
      , color:              l.getColor()
      });
    }, this);
    //Done:
    _.extend(this.model, {mapsData: data});
  }
  /***/
, render: function(){
    if(App.pageState.isPageView(this)){
      //Rendering the template:
      this.$el.html(App.templateStorage.render('MapView', {MapView: this.model}));
      //Binding click events:
      this.bindEvents();
      //Setting mapsData to map model:
      App.map.setModel(this.model.mapsData);
      //Displaying stuff:
      this.$el.removeClass('hide');
      $('#map_canvas').removeClass('hide');
    }else{
      this.$el.addClass('hide');
      $('#map_canvas').addClass('hide');
    }
  }
  /***/
, route: function(study, word, languages){
    console.log('MapView.route('+study+', '+word+', '+languages+')');
    var pv = this.getKey();
    //Setting the study:
    App.study.setStudy(study).always(function(){
      //Setting the word:
      App.wordCollection.setChoiceByKey(word);
      //Setting the languages:
      App.languageCollection.setSelectedByKey(App.router.parseArray(languages));
      //Set this pageView as active:
      App.pageState.setPageView(pv);
      //Render:
      App.views.renderer.render();
    });
  }
  /**
    Kept from the older views/MapView,
    this method makes sure all WordOverlayViews are displayed on the map correctly.
  */
, renderMap: function(){
    this.adjustCanvasSize();
    //Delayed centerRegion:
    var t = this, tid = window.setTimeout(function(){
      t.centerRegion();
      window.clearTimeout(tid);
    }, 1000);
    this.wordOverlayViews = _.map(
      App.map.get('wordOverlays')
    , function(wo){
        return wo.get('view');
      //FIXME remove commented
      //return new WordOverlayView({
      //  el: this.map, model: wo});
      }
    , this
    );
  }
  /**
    A method to make sure that the canvas size equals the maximum size possible in the current browser window.
  */
, adjustCanvasSize: function(){
    var canvas = $('#map_canvas')
      , offset = canvas.offset();
    if(canvas.length === 0) return;
    canvas.css('height', window.innerHeight - offset.top - 1 + 'px');
    google.maps.event.trigger(this.map, "resize");
  }
  /**
    Since the render method replaces some elements,
    it makes sense to have a method that binds all events for them,
    so that the event callbacks can easily be setup again.
  */
, bindEvents: function(){
    var events = {
      '#map_menu_viewAll':        'saveSelection'
    , '#map_menu_viewLast':       'loadSelection'
    , '#map_menu_zoomCenter':     'centerDefault'
    , '#map_menu_zoomCoreRegion': 'centerRegion'
    }, t = this;
    _.each(events, function(mName, tgt){
      this.$(tgt).click(function(){
        t[mName].call(t);
      });
    });
  }
  /**
    Centers the Map on the given default.
  */
, centerDefault: function(){
    this.map.fitBounds(App.map.get('defaultBounds'));
    $('#map_menu_zoomCenter').addClass('selected');
    $('#map_menu_zoomCoreRegion').removeClass('selected');
  }
  /**
    Centers the Map on the given region.
  */
, centerRegion: function(){
    this.map.fitBounds(App.map.get('regionBounds'));
    $('#map_menu_zoomCoreRegion').addClass('selected');
    $('#map_menu_zoomCenter').removeClass('selected');
  }
  //FIXME is this method still necessary/usefull?
, saveSelection: function(){
    var save = $('div#saveLocation').attr('href');
    localStorage['maps_userSelection'] = save;
  }
  //FIXME is this method still necessary/usefull?
, loadSelection: function(e){
    if(localStorage['maps_userSelection']){
      window.location.href = localStorage['maps_userSelection'];
      e.preventDefault();
    }
  }
  /**
    Fills a PlaySequence with currently displayed entries from the map in the given direction.
  */
, fillPSeq: function(direction, playSequence){
    var wos = App.map.sortWordOverlays(direction);
    _.chain(wos).filter(function(wo){
      var view = wo.get('view');
      if(wo.get('added') && view)
        return view.onScreen();
      return false;
    }).each(function(wo){
      playSequence.add(wo.getAudio());
    });
  }
  /**
    A method to compute a BoundingBox for the div that the map_canvas belongs to, relative to the browser viewport.
    The algorithm to do so comes from http://stackoverflow.com/questions/211703/is-it-possible-to-get-the-position-of-div-within-the-browser-viewport-not-withi
  */
, getBBox: function(){
    var e = this.div, offset = {x:0,y:0};
    //We traverse the parents of e to accumulate it's offsets:
    while(e){
      offset.x += e.offsetLeft;
      offset.y += e.offsetTop;
      e = e.offsetParent;
    }
    //We factor in the current scroll positions/page offsets:
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
    //We complete the representation of our BBox:
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
