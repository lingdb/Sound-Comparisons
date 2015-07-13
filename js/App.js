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
    'jquery.nanoscroller': {deps: ['jquery']}
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
    'jquery'
  , 'views/AudioLogic'
  , 'backbone'
  , 'models/Colors'
  , 'models/ContributorCategories'
  , 'collections/ContributorCollection'
  , 'models/DataStorage'
  , 'models/Defaults'
  , 'collections/FamilyCollection'
  , 'views/HideLinks'
  , 'views/IPAKeyboardView'
  , 'collections/LanguageCollection'
  , 'collections/LanguageStatusTypeCollection'
  , 'models/LoadingBar'
  , 'views/LoadModalView'
  , 'models/Logger'
  , 'models/Map'
  , 'collections/MeaningGroupCollection'
  , 'models/PageState'
  , 'views/PlaySequenceView'
  , 'collections/RegionCollection'
  , 'collections/RegionLanguageCollection'
  , 'views/render/Renderer'
  , 'Router'
  , 'views/SetupBarView'
  , 'models/SoundDownloader'
  , 'models/SoundPlayOption'
  , 'views/SoundPlayOptionView'
  , 'models/Study'
  , 'models/StudyWatcher'
  , 'models/TemplateStorage'
  , 'models/TranscriptionMap'
  , 'collections/TranscriptionSuperscriptCollection'
  , 'models/TranslationStorage'
  , 'collections/WordCollection'
  ], function(
    $
  , AudioLogic
  , Backbone
  , Colors
  , ContributorCategories
  , ContributorCollection
  , DataStorage
  , Defaults
  , FamilyCollection
  , HideLinks
  , IPAKeyboardView
  , LanguageCollection
  , LanguageStatusTypeCollection
  , LoadingBar
  , LoadModalView
  , Logger
  , Map
  , MeaningGroupCollection
  , PageState
  , PlaySequenceView
  , RegionCollection
  , RegionLanguageCollection
  , Renderer
  , Router
  , SetupBarView
  , SoundDownloader
  , SoundPlayOption
  , SoundPlayOptionView
  , Study
  , StudyWatcher
  , TemplateStorage
  , TranscriptionMap
  , TranscriptionSuperscriptCollection
  , TranslationStorage
  , WordCollection
  ){
    $(function(){
      //Building the App singleton:
      window.App = {storage: window.sessionStorage};
      _.extend(window.App, {pageState: new PageState()});
      //PageState before rest bc Selection needs it
      _.extend(window.App, {
        contributorCategories: new ContributorCategories()
      , contributorCollection: new ContributorCollection()
      , colors: new Colors()
      , dataStorage: new DataStorage()
      , defaults: new Defaults()
      , familyCollection: new FamilyCollection()
      , languageCollection: new LanguageCollection()
      , languageStatusTypeCollection: new LanguageStatusTypeCollection()
      , logger: new Logger()
      , meaningGroupCollection: new MeaningGroupCollection()
      , regionCollection: new RegionCollection()
      , regionLanguageCollection: new RegionLanguageCollection()
      , router: new Router()
      , soundDownloader: new SoundDownloader()
      , setupBar: new LoadingBar({
          segments: 6 // TranslationStorage: 1, DataStorage: 3, TemplateStorage: 1, Study: 1
        })
      , studyWatcher: new StudyWatcher()
      , study: new Study()
      , soundPlayOption: new SoundPlayOption()
      , templateStorage: new TemplateStorage()
      , transcriptionMap: new TranscriptionMap()
      , transcriptionSuperscriptCollection: new TranscriptionSuperscriptCollection()
      , translationStorage: new TranslationStorage()
      , views: {}
      , wordCollection: new WordCollection()
      });
      if(typeof(Map) !== 'undefined'){
        _.extend(window.App, {map: new Map()});
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
      App.views.hideLinks = new HideLinks();
      App.views.ipaKeyboardView = new IPAKeyboardView({
        el: $('#ipaKeyboard')});
      App.views.soundPlayOptionView = new SoundPlayOptionView({
        el: $('#topmenuSoundOptions')
      , model: App.soundPlayOption
      });
      App.views.audioLogic = new AudioLogic({el: $('#audioLogic')});
      App.views.setupBar = new SetupBarView({
        el: $('#appSetup'), model: App.setupBar
      });
      App.views.playSequenceView = new PlaySequenceView();
      App.views.renderer = new Renderer({el: $('body')});
      App.views.loadModalView = new LoadModalView({el: $('#loadModal')});
    });
  });
