"use strict";
requirejs.config({
  baseUrl: 'js'
, paths: {//Paths for extern scripts
    'jquery': 'extern/jquery.min'
  , 'jquery.nanoscroller': 'extern/jquery.nanoscroller.min'
  , 'jquery.cookie': 'extern/jquery.cookie'
  , 'jquery.scrollTo': 'extern/jquery.scrollTo-1.4.2-min'
  , 'jquery.mousewheel': 'extern/jquery.mousewheel.min'
  , 'jquery.json': 'extern/jquery.json-2.3.min'
  , 'underscore': 'extern/underscore-min'
  , 'backbone': 'extern/backbone-min'
  , 'bootstrap': 'extern/bootstrap'
  , 'Blob': 'extern/Blob'
  , 'FileSaver': 'extern/FileSaver.min'
  , 'Mustache': 'extern/mustache'
  , 'LZString': 'extern/lz-string-1.3.3-min'
  , 'QueryString': 'extern/QueryString'
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
  }
});
requirejs([
  'require','jquery','backbone','underscore'
, 'collections/ContributorCollection'
, 'collections/FamilyCollection'
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
, 'models/Map'
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
          segments: 6 // TranslationStorage: 1, DataStorage: 3, TemplateStorage: 1, Study: 1
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
      if(typeof(Map) !== 'undefined'){
        _.extend(window.App, {map: new (require('models/Map'))()});
      }
      //Making sure TranslationStorage does it's thing:
      App.translationStorage.init();
      //Listening for changing global data:
      _.each(['contributorCategories','contributorCollection','languageStatusTypeCollection','meaningGroupCollection','transcriptionSuperscriptCollection'], function(l){
        this.dataStorage.on('change:global', this[l].update, this[l]);
      }, App);
      //Listening for changing studies:
      App.studyWatcher.listen();
      App.studyWatcher.on('change:study', function(){
        App.dataStorage.loadStudy(App.studyWatcher.get('study'));
      });
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
    });
  });
