/* global document: false */
"use strict";
define(['views/render/SubView',
        'views/SoundControlView',
        'views/render/WordView',
        'views/MouseTrackView',
        'views/WordMarker',
        'models/Loader',
        'leaflet','leaflet-markercluster','leaflet-providers'],
       function(SubView,
                SoundControlView,
                WordView,
                MouseTrackView,
                WordMarker,
                Loader,
                L){
  return SubView.extend({
    /***/
    initialize: function(){
      //Data representation created by update methods:
      this.model = {}; // Notice that we also make heavy use of App.map
      //Connecting to the router
      App.router.on('route:mapView', this.route, this);
      //Setting leaflet imagePath:
      L.Icon.Default.imagePath = '/img/leaflet.js/';
      //Map setup:
      this.div = document.getElementById("map_canvas");
      this.map = L.map(this.div).setView([54.92, 1.875], 2);
      //Specifying tileLayer:
      if(Loader.isOnline){
        var baseLayers = {
          'Esri World Imagery': L.tileLayer.provider('Esri.WorldImagery').addTo(this.map),
          'Esri NatGeo World Map': L.tileLayer.provider('Esri.NatGeoWorldMap'),
          'Open Street Map': L.tileLayer.provider('OpenStreetMap'),
          'Esri World Topo Map': L.tileLayer.provider('Esri.WorldTopoMap'),
          'Esri De Lorme': L.tileLayer.provider('Esri.DeLorme'),
          'Esri World Shaded Relief': L.tileLayer.provider('Esri.WorldShadedRelief')
        };
        L.control.layers(baseLayers).addTo(this.map);
      }else{
        L.tileLayer('mapnik/{z}/{x}/{y}.png', {
            minZoom: 0
          , maxZoom: 17
          , attribution: "<a href='https://www.mapbox.com/about/maps/' "
                           + "target='_blank'>&copy; Mapbox</a>"
                       + "<a href='https://openstreetmap.org/about/' "
                           + "target='_blank'>&copy; OpenStreetMap</a>"
                       + "<a class='mapbox-improve-map' "
                           + "href='https://www.mapbox.com/map-feedback/' "
                           + "target='_blank'>Improve this map</a>"
          , opacity: 0.85
        }).addTo(this.map);
      }
      //Creating marker layer:
      this.markers = L.layerGroup(); // FIXME replace with markerClusterGroup
      this.markers.addLayers = function(ls){_.each(ls, this.addLayer, this);};
      this.markers.removeLayers = function(ls){_.each(ls, this.removeLayer, this);};
//		this.markers = L.markerClusterGroup({
//      maxClusterRadius: 120,
//      iconCreateFunction: WordMarker.mkCluster,
//      //Set flags:
//      animate: true,
//      showCoverageOnHover: true,
//      spiderfyOnMaxZoom: false,
//      zoomToBoundsOnClick: false
//		});
      this.map.addLayer(this.markers);

      this.fixMap().renderMap();
      //SoundControlView:
      this.soundControlView = new SoundControlView({
        el: this.map, model: this.model});
      if(!_.isUndefined(MouseTrackView)){
        this.mouseTrackView = new MouseTrackView({
          el: this.map, model: this});
      }
      //Window resize
      var view = this;
      $(window).resize(function(){view.adjustCanvasSize();});
      //Handle zooming via mouse on clicking the map:
      this.map.on('mousedown', _.bind(this.setScrollWheel, this, true));
      $('body').on('mousedown', function(e){
        var tgt = $(e.target)
          , onMap = tgt.parents('#map_canvas').length > 0;
        if(!onMap && tgt.attr('id') !== 'map_canvas'){
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
      var data = {
        transcriptions: []
      , regionZoom: App.study.getMapZoomCorners()
      }, word = App.wordCollection.getChoice();
      //Iterating languages:
      var languages = App.pageState.get('mapViewIgnoreSelection')
                    ? App.languageCollection.models
                    : App.languageCollection.getSelected();
      _.each(languages, function(l){
        var latlng = l.getLatLng();
        if(latlng === null) return;
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
        , latlng:             latlng
        , historical:         l.isHistorical() ? 1 : 0
        , phoneticSoundfiles: psf
        , langName:           l.getShortName()
        , languageLink:       'href="'+App.router.linkLanguageView({language: l})+'"'
        , familyIx:           l.getFamilyIx()
        , color:              proxyColor(l.getColor()).color
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
        //Updating SoundControlView:
        this.soundControlView.update();
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
      if('mapsData' in this.model){
        var ts = this.model.mapsData.transcriptions
          , ms = {} // Newly added markers
        _.each(ts, function(tData){
          tData = WordMarker.mkWordMarker(this.map, tData);
          tData.marker.__newlyAdded = true; // Marking the ones we still need:
          ms[tData.marker.id] = tData.marker;
        }, this);
        // Adding ms, duplicates filtered by markercluster:
        this.markers.addLayers(_.values(ms));
        // Removing no longer wanted markers:
        var removeMarkers = [];
        _.each(this.markers.getLayers(), function(m){
          if(!(m.id in ms)){
            removeMarkers.push(m);
          }
        }, this);
        this.markers.removeLayers(removeMarkers);
        //Fixing size of newly added markers:
        window.setTimeout(function(){
          _.each(_.values(ms), function(m){m.fixSize();});
        }, 1);
      }
      this.adjustCanvasSize();
      this.centerRegion();
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
        j = window.setTimeout(disable, 5000);
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
      var defaultBounds = this.getDefaultBounds();
      this.map.fitBounds(defaultBounds);
      $('#map_menu_zoomCenter').addClass('selected');
      $('#map_menu_zoomCoreRegion').removeClass('selected');
      return this;
    }
    /**
      @return this for chaining
      Centers the Map on the given region.
    */
  , centerRegion: function(){
      var bnds = this.getRegionBounds();
      if(!_.isEmpty(bnds)){
        this.map.fitBounds(bnds);
        $('#map_menu_zoomCoreRegion').addClass('selected');
        $('#map_menu_zoomCenter').removeClass('selected');
      }
      return this;
    }
    /**
      Per default, the map doesn't care for the scroll wheel,
      but this functions allows us to change that.
    */
  , setScrollWheel: function(use){
      if(use){
        this.map.scrollWheelZoom.enable();
      }else{
        this.map.scrollWheelZoom.disable();
      }
      return this.map.scrollWheelZoom.enabled();
    }
    /**
      Compute the regionBounds for the current study.
      @return LatLngBounds | null
    */
  , getRegionBounds: function(){
      var study = window.App.study;
      if(study.isReady()){
        var get = _.bind(study.get, study);
        var tl = L.latLng(get('DefaultTopLeftLat'), get('DefaultTopLeftLon'))
          , br = L.latLng(get('DefaultBottomRightLat'), get('DefaultBottomRightLon'));
        return L.latLngBounds([tl, br]);
      }
      return null;
    }
    /**
      Compute the defaultBounds for the current set of transcriptions.
      @return LatLngBounds
    */
  , getDefaultBounds: function(){
      return L.latLngBounds(
        _.map(this.model.mapsData.transcriptions, function(t){
          return t.latlng;
        }, this));
    }
    /**
      @param l Language
      If the language is not on the map,
      we zoom/center to the bounding box of
      the 11 closest languages to the given one.
      Otherwise nothing happens.
    */
  , boundLanguage: function(l){
      var ll = l.getLatLng();
      if(!this.map.getBounds().contains(ll)){
        var lls = _.chain(window.App.languageCollection.models)
                   .map(function(l){return l.getLatLng();})
                   .sortBy(function(x){return ll.distanceTo(x);})
                   .first(11).value();
        this.map.fitBounds(L.latLngBounds(lls));
      }
    }
    /**
      @param l Language
      Method to zoom in on the location of a single language.
    */
  , zoomLanguage: function(l){
      var ll = l.getLatLng();
      if(ll !== null){
        this.map.setView(l.getLatLng(), 8);
      }
    }
    /**
      @param ls [Language]
      Method to zoom in on the bounding box of an array of languages.
    */
  , zoomLanguages: function(ls){
      var bnds = L.latLngBounds(_.map(ls, function(l){
        return l.getLatLng();
      }, this));
      this.map.fitBounds(bnds);
    }
    /**
      @param l Language
      This function highlights a language on the map.
    */
  , highlight: function(l){
      console.log('FIXME reimplement MapView.highlight()');
      //FIXME reimplement
    }
  });
});
