/* global document: false */
"use strict";
define(['views/render/SubView',
        'views/SoundControlView',
        'views/render/WordView',
        'views/MouseTrackView',
        'leaflet','leaflet-markercluster'],
       function(SubView, SoundControlView, WordView, MouseTrackView, leaflet){
  return SubView.extend({
    /***/
    initialize: function(){
      //Data representation created by update methods:
      this.model = {}; // Notice that we also make heavy use of App.map
      //Connecting to the router
      App.router.on('route:mapView', this.route, this);
      //Setting leaflet imagePath:
      leaflet.Icon.Default.imagePath = '/img/leaflet.js/';
      //Map setup:
      this.div = document.getElementById("map_canvas");
      this.map = leaflet.map(this.div).setView([51.505, -0.09], 13);
      //Specifying tileLayer:
      leaflet.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
      }).addTo(this.map);
      //Setting a marker for testing:
      leaflet.marker([51.5, -0.09]).addTo(this.map)
          .bindPopup('A pretty CSS3 popup.<br> Easily customizable.')
          .openPopup();

      this.fixMap().renderMap();
      //SoundControlView:
      this.soundControlView = new SoundControlView({
        el: this.map, model: this});
      if(!_.isUndefined(MouseTrackView)){
        this.mouseTrackView = new MouseTrackView({
        el: this.map, model: this});
      }
      //Window resize
      var view = this;
      $(window).resize(function(){view.adjustCanvasSize();});
// FIXME REIMPLEMENT MAP CODE:
//    google.maps.event.addListener(this.map, 'zoom_changed', function(){
//      App.map.placeWordOverlays();
//    });
//    //Handle zooming via mouse on clicking the map:
//    google.maps.event.addListener(this.map, 'mousedown', function(){
//      view.setScrollWheel(true);
//    });
      $('body').on('mousedown', function(e){
        var onMap = $(e.target).parents('#map_canvas').length > 0;
        if(!onMap){
          view.setScrollWheel(false);
        }
      });
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
    /***/
  , updateLinks: function(){
      var lc = App.languageCollection;
      _.extend(this.model, {
        viewAll:     'href="'+App.router.linkConfig({MapViewIgnoreSelection: true})+'"'
      , viewLast:    'href="'+App.router.linkConfig({MapViewIgnoreSelection: false})+'"'
      , allSelected: lc.length === lc.getSelected().length
      });
    }
    /***/
  , updateMapsData: function(){
      return; // FIXME REIMPLEMENT?!
      var data = {
        transcriptions: []
      , regionZoom: App.study.getMapZoomCorners()
      }, word = App.wordCollection.getChoice();
      //Iterating languages:
      var languages = App.pageState.get('mapViewIgnoreSelection') ? App.languageCollection.models : App.languageCollection.getSelected();
      _.each(languages, function(l){
        var latlon = l.getLocation();
        if(latlon === null) return;
        var tr = App.transcriptionMap.getTranscription(l, word);
        //Creating psf entries:
        var psf = [];
        if(tr !== null){
          psf = _.map(tr.getPhonetics(), function(p){
            return { phonetic:   p.phonetic
                   , soundfiles: $.parseJSON(p.srcs) };
          }, this);
        }
        //proxyColor added for #364
        var proxyColor = function(o){
          if(App.study.getId() === 'Malakula'){
            o.color = '#CFFF7C';
          }
          return o;
        };
        //The complete structure:
        data.transcriptions.push({
          altSpelling:        (tr !== null) ? tr.getAltSpelling() : ''
        , translation:        word.getNameFor(l)
        , lat:                latlon[0]
        , lon:                latlon[1]
        , historical:         l.isHistorical() ? 1 : 0
        , phoneticSoundfiles: psf
        , langName:           l.getShortName()
        , languageLink:       'href="'+App.router.linkLanguageView({language: l})+'"'
        , familyIx:           l.getFamilyIx()
        , color:              proxyColor(l.getColor())
        , languageIx:         l.getId()
        });
      }, this);
      //Done:
      _.extend(this.model, {mapsData: data});
    }
    /***/
  , render: function(o){
      o = _.extend({renderMap: true}, o);
      if(App.pageState.isPageView(this)){
        //Rendering the template:
        this.$el.html(App.templateStorage.render('MapView', {MapView: this.model}));
        //Binding click events:
        this.bindEvents();
        //FIXME REIMPLEMENT
        //Updating SoundControlView:
        //this.soundControlView.update();
        //Setting mapsData to map model:
        //App.map.setModel(this.model.mapsData);
        //Displaying stuff:
        this.$el.removeClass('hide');
        $('#map_canvas').removeClass('hide');
        if(o.renderMap){
          this.renderMap();
        }
      }else{
        this.$el.addClass('hide');
        $('#map_canvas').addClass('hide');
      }
    }
    /***/
  , route: function(siteLanguage, study, word, languages){
      var parse = App.router.parseString;
      study = parse(study);
      word = parse(word);
      console.log('MapView.route('+study+', '+word+', '+languages+')');
      var t = this;
      //Setting siteLanguage and study:
      this.loadBasic(siteLanguage, study).always(function(){
        var pv = t.getKey();
        //Setting the word:
        App.wordCollection.setChoiceByKey(word);
        //Setting the languages:
        App.languageCollection.setSelectedByKey(App.router.parseArray(languages), pv);
        //Handling the renderMapFlag:
        t.renderMapFlag = App.pageState.isPageView(pv);
        //Changing pageView if necessary:
        if(!t.renderMapFlag) App.pageState.setPageView(pv);
        //Render:
        App.views.renderer.render();
      });
    }
    /**
      This method fixes the view level to the regionBounds
      once the study is changed or it is called the first time.
      This method also calls adjustCanvasSize.
    */
  , renderMap: function(){
      return; // FIXME REIMPLEMENT?!
      this.renderMapFirst = true; // Tracking if we want the first case of renderMap.
      this.renderMap = function(){
        /*
          It would be nice to depend on events rather than a Timeout,
          but events appeared to be rather annoying while this is simple.
        */
        if(this.renderMapFirst){
          var t = this;
          window.setTimeout(function(){
            if(!t.renderMapFirst) return;
            t.renderMapFirst = false;
            t.adjustCanvasSize();
            t.centerRegion();
          }, 10000);
        }else{
          this.adjustCanvasSize();
          this.centerRegion();
        }
      };
      return this.renderMap();
    }
    /**
      @return this for chaining
      This function was created to fix #57.
      I've tried different approaches to make sure the map is displayed correctly,
      but they all failed:
      1: Listening to various map events to trigger resize when appropriate
      2: Adjusting the bootstrap CSS to make sure images aren't distorted
      3: Listening to combinations of events by the use of promises
      4: Having a general timeout of 10 sec. to resize the map
      To work around this, the following function uses a rather hackish approach,
      but works:
      We trigger adjustCanvasSize and centerRegion repeatedly by interval,
      until one of two things happens:
      1: Events are fired that indicate we've done the right thing
      2: We've done this for 10s and nothing changed
    */
  , fixMap: function(){
      (function(t){
        var i = null, j = null, ls = [];
        //Disable the intervall:
        var disable = function(){
          if(i !== null){
            window.clearInterval(i);
            i = null;
          }
          if(j !== null){
            window.clearTimeout(j);
            j = null;
          }
          _.each(ls, function(l){ google.maps.event.removeListener(l); });
        };
        //Timeout to make sure disable is called:
        j = window.setTimeout(disable, 10000);
        //Getting everything running:
        i = window.setInterval(function(){
          t.adjustCanvasSize().centerRegion();
        }, 1000);
      })(this);
      return this;
    }
    /**
      A method to make sure that the canvas size equals the maximum size possible in the current browser window.
    */
  , adjustCanvasSize: function(){
      var canvas = $('#map_canvas')
        , offset = canvas.offset();
      if(canvas.length !== 0){
        canvas.css('height', window.innerHeight - offset.top - 1 + 'px');
        this.map.invalidateSize();
      }
      return this;
    }
    /**
      Since the render method replaces some elements,
      it makes sense to have a method that binds all events for them,
      so that the event callbacks can easily be setup again.
    */
  , bindEvents: function(){
      var t = this;
      _.each({'#map_menu_zoomCenter': 'centerDefault', '#map_menu_zoomCoreRegion': 'centerRegion'}
      , function(mName, tgt){
        this.$(tgt).click(function(){
          t[mName].call(t);
        });
      }, this);
    }
    /**
      @return this for chaining
      Centers the Map on the given default.
    */
  , centerDefault: function(){
      this.map.fitBounds(App.map.get('defaultBounds'));
      $('#map_menu_zoomCenter').addClass('selected');
      $('#map_menu_zoomCoreRegion').removeClass('selected');
      return this;
    }
    /**
      @return this for chaining
      Centers the Map on the given region.
    */
  , centerRegion: function(){
      return this; // FIXME REIMPLEMENT
      var bnds = App.map.get('regionBounds');
      if(bnds !== null){
        this.map.fitBounds(App.map.get('regionBounds'));
        $('#map_menu_zoomCoreRegion').addClass('selected');
        $('#map_menu_zoomCenter').removeClass('selected');
      }
      return this;
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
    /**
      Per default, the map doesn't care for the scroll wheel,
      but this functions allows us to change that.
    */
  , setScrollWheel: function(use){
      if(use){
        map.scrollWheelZoom.enable();
      }else{
        map.scrollWheelZoom.disable();
      }
      return this.map.scrollWheelZoom.enabled();
    }
    /**
      @param l Language
      If the language is not on the map,
      we zoom/center to the bounding box of
      the 11 closest languages to the given one.
      Otherwise nothing happens.
    */
  , boundLanguage: function(l){
      this.renderMapFirst = false;//Making sure no timeout zooms along.
      var ll = l.getLatLng();
      if(!this.map.getBounds().contains(ll)){
        var bounds = new google.maps.LatLngBounds()
          , xs = window.App.languageCollection.map(function(l){return l.getLatLng();});
        xs = _.sortBy(xs, function(x){
          return google.maps.geometry.spherical.computeDistanceBetween(ll,x);
        });
        _.each(_.first(xs, 11), function(x){bounds.extend(x);});
        this.map.fitBounds(bounds);
      }
    }
    /**
      @param l Language
      Method to zoom in on the location of a single language.
    */
  , zoomLanguage: function(l){
      this.renderMapFirst = false;//Making sure no timeout zooms along.
      var ll = l.getLatLng();
      this.map.setCenter(ll);
      this.map.setZoom(8);
    }
    /**
      @param ls [Language]
      Method to zoom in on the bounding box of an array of languages.
    */
  , zoomLanguages: function(ls){
      this.renderMapFirst = false;//Making sure no timeout zooms along.
      var bounds = new google.maps.LatLngBounds();
      _.each(ls, function(l){bounds.extend(l.getLatLng());});
      this.map.fitBounds(bounds);
    }
  });
});
