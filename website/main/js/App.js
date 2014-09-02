$(function(){
  //Building the App singleton:
  window.App = {
    contributorCollection: new ContributorCollection()
  , dataStorage: new DataStorage()
  , downloadOptions: new DownloadOptions()
  , familyCollection: new FamilyCollection()
  , languageCollection: new LanguageCollection()
  , languageStatusTypeCollection: new LanguageStatusTypeCollection()
  , linkInterceptor: new LinkInterceptor()
  , loadingBar: new LoadingBar()
  , logger: new Logger()
  , map: new Map()
  , meaningGroupCollection: new MeaningGroupCollection()
  , regionCollection: new RegionCollection()
  , regionLanguageCollection: new RegionLanguageCollection()
  , setupBar: new LoadingBar({segments: 4})
  , studyWatcher: new StudyWatcher()
  , study: new Study()
  , soundPlayOption: new SoundPlayOption()
  , templateStorage: new TemplateStorage()
  , transcriptionMap: new TranscriptionMap()
  , transcriptionSuperscriptCollection: new TranscriptionSuperscriptCollection()
  , translationStorage: new TranslationStorage()
  , viewWatcher: new ViewWatcher()
  , views: {}
  , wordCollection: new WordCollection()
  };
  //Listening for changing global data:
  _.each(['contributorCollection','languageStatusTypeCollection','meaningGroupCollection','transcriptionSuperscriptCollection'], function(l){
    this.dataStorage.on('change:global', this[l].update, this[l]);
  }, App);
  //Listening for changing studies:
  App.studyWatcher.on('change:study', App.dataStorage.loadStudy, App.dataStorage);
  _.each(['study','familyCollection','languageCollection','regionCollection','regionLanguageCollection','transcriptionMap','wordCollection']
    , function(l){this.dataStorage.on('change:study', this[l].update, this[l]);}, App);
  //Creating views:
  App.views.downloadOptionView = new DownloadOptionView({
    el: $('body'), model: App.downloadOptions
  });
  App.views.hideLinks = new HideLinks();
  App.views.ipaKeyboardView = new IPAKeyboardView({
    el: $('#ipaKeyboard')});
  App.views.soundPlayOptionView = new SoundPlayOptionView({
    el: $('#topmenuSoundOptions')
  , model: App.soundPlayOption
  });
  App.views.audioLogic = new AudioLogic();
  App.views.mapView = new MapView({
    el: $('#contentArea')
  , model: App.map
  });
  App.views.wordlistFilter = new WordlistFilter();
  App.views.loadingBar = new LoadingBarView({
    el: $('.loadingBar')
  , model: App.loadingBar
  });
  App.views.setupBar = new SetupBarView({
    el: $('#appSetup')
  , model: App.setupBar
  });
  App.views.playSequenceView = new PlaySequenceView();
  App.views.renderer = new Renderer({el: $('body')});
  App.views.singleLanguageView = new SingleLanguageView();
  App.views.whoAreWeView = new WhoAreWeView();
});
