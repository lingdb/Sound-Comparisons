"use strict";
/* global requirejs, App */
requirejs.config({
  baseUrl: 'js'
, paths: {//Paths for extern scripts
    'jquery': 'bower_components/jquery/dist/jquery.min'
  , 'jquery.nanoscroller': 'bower_components/nanoscroller/bin/javascripts/jquery.nanoscroller.min'
  , 'jquery.cookie': 'extern/jquery.cookie' // FIXME replaced by https://github.com/js-cookie/js-cookie
  , 'jquery.scrollTo': 'bower_components/jquery.scrollTo/jquery.scrollTo.min'
  , 'jquery.mousewheel': 'bower_components/jquery-mousewheel/jquery.mousewheel.min'
  , 'jquery.json': 'extern/jquery.json-2.3.min'
  , 'underscore': 'bower_components/underscore/underscore-min'
  , 'backbone': 'bower_components/backbone/backbone-min'
  , 'bootstrap': 'extern/bootstrap.min'
  , 'bootbox': 'extern/bootbox.min'
  , 'Blob': 'extern/Blob' // FIXME replace with bower Blob?
  , 'FileSaver': 'extern/FileSaver.min' // FIXME replace with bower FileSaver?
  , 'Mustache': 'bower_components/mustache.js/mustache.min'
  , 'LZString': 'extern/lz-string-1.3.3-min'
  , 'QueryString': 'extern/QueryString'
  , 'i18n': 'extern/i18next.amd.withJQuery-1.10.1' // Compressor depends on this
  , 'markdown-it': 'bower_components/markdown-it/dist/markdown-it.min'
  , 'leaflet': 'bower_components/leaflet/dist/leaflet'
  , 'leaflet-markercluster': 'bower_components/leaflet.markercluster/dist/leaflet.markercluster'
  , 'leaflet.dom-markers': 'bower_components/Leaflet.DomMarkers/src/leaflet.dom-markers'
  , 'leaflet-providers': 'bower_components/leaflet-providers/leaflet-providers'
  , 'leaflet.zoomslider': 'bower_components/leaflet-zoomslider/src/L.Control.Zoomslider'
  }
, shim: {//Dependencies and magic for extern scripts
    'jquery': {exports: '$'}
  , 'jquery.nanoscroller': {deps: ['jquery']}
  , 'jquery.cookie': {deps: ['jquery']}
  , 'jquery.scrollTo': {deps: ['jquery']}
  , 'jquery.mousewheel': {deps: ['jquery']}
  , 'jquery.json': {deps: ['jquery']}
  , 'underscore': {exports: '_'}
  , 'backbone': {
      deps: ['underscore', 'jquery']
    , exports: 'Backbone'
    }
  , 'bootstrap': {deps: ['jquery']}
  , 'Blob': {}
  , 'FileSaver': {exports: 'FileSaver'}
  , 'Mustache': {exports: 'Mustache'}
  , 'LZString': {exports: 'LZString'}
  , 'QueryString': {exports: 'QueryString'}
  , 'leaflet-markercluster': {deps: ['leaflet']}
  , 'leaflet.dom-markers': {deps: ['leaflet']}
  , 'leaflet-providers': {deps: ['leaflet']}
  }
});
requirejs([
  'require','jquery','backbone','underscore'
, 'collections/ContributorCollection'
, 'collections/FamilyCollection'
, 'collections/FilteredWordCollection'
, 'collections/LanguageCollection'
, 'collections/LanguageStatusTypeCollection'
, 'collections/MeaningGroupCollection'
, 'collections/RegionCollection'
, 'collections/RegionLanguageCollection'
, 'collections/TranscriptionSuperscriptCollection'
, 'collections/WordCollection'
, 'models/Colors'
, 'models/ContributorCategories'
, 'models/DataStorage'
, 'models/Defaults'
, 'models/LoadingBar'
, 'models/Logger'
, 'models/PageState'
, 'models/SoundDownloader'
, 'models/SoundPlayOption'
, 'models/Study'
, 'models/StudyWatcher'
, 'models/TemplateStorage'
, 'models/TranscriptionMap'
, 'models/TranslationStorage'
, 'Router'
, 'views/AudioLogic'
, 'views/HideLinks'
, 'views/IPAKeyboardView'
, 'views/LoadModalView'
, 'views/PlaySequenceView'
, 'views/render/Renderer'
, 'views/SetupBarView'
, 'views/ShortLinkModalView'
, 'views/SoundPlayOptionView'
], function(require, $, Backbone, _){
    $(function(){
      //Building the App singleton:
      window.App = {storage: window.sessionStorage};
      _.extend(window.App, {pageState: new (require('models/PageState'))()});
      //PageState before rest bc Selection needs it
      _.extend(window.App, {
        contributorCategories: new (require('models/ContributorCategories'))()
      , contributorCollection: new (require('collections/ContributorCollection'))()
      , colors: new (require('models/Colors'))()
      , dataStorage: new (require('models/DataStorage'))()
      , defaults: new (require('models/Defaults'))()
      , familyCollection: new (require('collections/FamilyCollection'))()
      , languageCollection: new (require('collections/LanguageCollection'))()
      , languageStatusTypeCollection: new (require('collections/LanguageStatusTypeCollection'))()
      , logger: new (require('models/Logger'))()
      , meaningGroupCollection: new (require('collections/MeaningGroupCollection'))()
      , regionCollection: new (require('collections/RegionCollection'))()
      , regionLanguageCollection: new (require('collections/RegionLanguageCollection'))()
      , router: new (require('Router'))()
      , soundDownloader: new (require('models/SoundDownloader'))()
      , setupBar: new (require('models/LoadingBar'))({
          segments: 5 // TranslationStorage: 1, DataStorage: 3, Study: 1
        })
      , studyWatcher: new (require('models/StudyWatcher'))()
      , study: new (require('models/Study'))()
      , soundPlayOption: new (require('models/SoundPlayOption'))()
      , templateStorage: new (require('models/TemplateStorage'))()
      , transcriptionMap: new (require('models/TranscriptionMap'))()
      , transcriptionSuperscriptCollection: new (require('collections/TranscriptionSuperscriptCollection'))()
      , translationStorage: new (require('models/TranslationStorage'))()
      , views: {}
      , wordCollection: new (require('collections/WordCollection'))()
      });
      //Adding FilteredWordCollection:
      App.filteredWordCollection = new (require('collections/FilteredWordCollection'))();
      //Making sure TranslationStorage does it's thing:
      App.translationStorage.init();
      //Listening for changing global data:
      _.each(['contributorCategories','contributorCollection','languageStatusTypeCollection','meaningGroupCollection','transcriptionSuperscriptCollection'], function(l){
        this.dataStorage.on('change:global', this[l].update, this[l]);
      }, App);
      //Listening for changing studies:
      App.studyWatcher.listen();
      _.each(['defaults','study','familyCollection','languageCollection','meaningGroupCollection','regionCollection','regionLanguageCollection','transcriptionMap','wordCollection']
        , function(l){this.dataStorage.on('change:study', this[l].update, this[l]);}, App);
      //Setting up callbacks for PageState:
      App.wordCollection.listenWordOrder();
      App.pageState.activate();
      //Start routing once setup finishes:
      App.setupBar.onFinish(function(){
        Backbone.history.start();
      });
      //Creating views:
      App.views.hideLinks = new (require('views/HideLinks'))();
      App.views.ipaKeyboardView = new (require('views/IPAKeyboardView'))({
        el: $('#ipaKeyboard')});
      App.views.soundPlayOptionView = new (require('views/SoundPlayOptionView'))({
        el: $('#topmenuSoundOptions')
      , model: App.soundPlayOption
      });
      App.views.audioLogic = new (require('views/AudioLogic'))({el: $('#audioLogic')});
      App.views.setupBar = new (require('views/SetupBarView'))({
        el: $('#appSetup'), model: App.setupBar
      });
      App.views.playSequenceView = new (require('views/PlaySequenceView'))();
      App.views.renderer = new (require('views/render/Renderer'))({el: $('body')});
      App.views.loadModalView = new (require('views/LoadModalView'))({el: $('#loadModal')});
      App.views.shortLinkModalView = new (require('views/ShortLinkModalView'))({el: $('#shortLinkModal')});
    });
  });
