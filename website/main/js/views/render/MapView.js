/***/
MapView = Renderer.prototype.SubView.extend({
  /***/
  initialize: function(){
    this.model = {};
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
    _.extend(this.model, {mapsData: JSON.stringify(data)});
  }
  /***/
, render: function(){
    if(App.pageState.isPageView(this)){
      this.$el.html(App.templateStorage.render('MapView', {MapView: this.model}));
      //FIXME trigger rebuilding the map, or something.
      this.$el.removeClass('hide');
    }else{
      this.$el.addClass('hide');
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
});
