$(function(){
  //Building the App singleton:
  window.App = {
    dataStorage: new DataStorage()
  , downloadOptions: new DownloadOptions()
  , familyCollection: new FamilyCollection()
  , languageCollection: new LanguageCollection()
  , languageStatusTypeCollection: new LanguageStatusTypeCollection()
  , linkInterceptor: new LinkInterceptor()
  , loadingBar: new LoadingBar()
  , logger: new Logger()
  , map: new Map()
  , pageWatcher: new PageWatcher()
  , regionCollection: new RegionCollection()
  , regionLanguageCollection: new RegionLanguageCollection()
  , studyWatcher: new StudyWatcher()
  , study: new Study()
  , soundPlayOption: new SoundPlayOption()
  , templateStorage: new TemplateStorage()
  , translationStorage: new TranslationStorage()
  , viewWatcher: new ViewWatcher()
  , views: {}
  , wordCollection: new WordCollection()
  };
  //Listening for changing global data:
  _.each(['languageStatusTypeCollection'], function(l){
    this.dataStorage.on('change:global', this[l].update, this[l]);
  }, App);
  //Listening for changing studies:
  App.studyWatcher.on('change:study', App.dataStorage.loadStudy, App.dataStorage);
  _.each(['study','familyCollection','languageCollection','regionCollection','regionLanguageCollection','wordCollection']
    , function(l){this.dataStorage.on('change:study', this[l].update, this[l]);}, App);
  //Creating views:
  App.views.downloadOptionView = new DownloadOptionView({
    el: $('body'), model: App.downloadOptions
  });
  App.views.hideLinks = new HideLinks({
    el: null, model: App.pageWatcher});
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
  App.views.parts = {
    topMenuView: new TopMenuView({
      model: App.templateStorage
    })
  , languageMenuView: new LanguageMenuView({
      model: App.templateStorage
    })
  , wordMenuView: new WordMenuView({
      model: App.templateStorage
    })
  , contentView: new ContentView({
      model: App.templateStorage
    })
  , headView: new HeadView({
      el: $('head')
    , model: App.templateStorage
    })
  };
  App.views.playSequenceView = new PlaySequenceView();
  App.views.singleLanguageView = new SingleLanguageView();
  App.views.whoAreWeView = new WhoAreWeView({
    model: App.pageWatcher
  });
});
