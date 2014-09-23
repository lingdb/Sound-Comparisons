$(function(){
  //Building the App singleton:
  window.App = {
    contributorCollection: new ContributorCollection()
  , dataStorage: new DataStorage()
  , defaults: new Defaults()
  , familyCollection: new FamilyCollection()
  , languageCollection: new LanguageCollection()
  , languageStatusTypeCollection: new LanguageStatusTypeCollection()
  , linkInterceptor: new LinkInterceptor()
  , logger: new Logger()
  , map: new Map()
  , meaningGroupCollection: new MeaningGroupCollection()
  , pageState: new PageState()
  , regionCollection: new RegionCollection()
  , regionLanguageCollection: new RegionLanguageCollection()
  , router: new Router()
  , setupBar: new LoadingBar({segments: 5})
  , studyWatcher: new StudyWatcher()
  , study: new Study()
  , soundPlayOption: new SoundPlayOption()
  , templateStorage: new TemplateStorage()
  , transcriptionMap: new TranscriptionMap()
  , transcriptionSuperscriptCollection: new TranscriptionSuperscriptCollection()
  , translationStorage: new TranslationStorage()
  , views: {}
  , wordCollection: new WordCollection()
  };
  //Listening for changing global data:
  _.each(['contributorCollection','languageStatusTypeCollection','meaningGroupCollection','transcriptionSuperscriptCollection'], function(l){
    this.dataStorage.on('change:global', this[l].update, this[l]);
  }, App);
  //Listening for changing studies:
  App.studyWatcher.on('change:study', App.dataStorage.loadStudy, App.dataStorage);
  _.each(['defaults','study','familyCollection','languageCollection','regionCollection','regionLanguageCollection','transcriptionMap','wordCollection']
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
  App.views.audioLogic = new AudioLogic();
//FIXME enable/rebuild later on
//App.views.wordlistFilter = new WordlistFilter();
  App.views.setupBar = new SetupBarView({
    el: $('#appSetup'), model: App.setupBar
  });
  App.views.playSequenceView = new PlaySequenceView();
  App.views.renderer = new Renderer({el: $('body')});
  App.views.singleLanguageView = new SingleLanguageView();
});
