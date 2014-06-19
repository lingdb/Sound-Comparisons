$(function(){
  //Building the App singleton:
  window.App = {
    linkInterceptor:    new LinkInterceptor()
  , loadingBar:         new LoadingBar()
  , logger:             new Logger()
  , map:                new Map()
  , pageWatcher:        new PageWatcher()
  , studyWatcher:       new StudyWatcher()
  , soundPlayOption:    new SoundPlayOption()
  , templateStorage:    new TemplateStorage()
  , translationStorage: new TranslationStorage()
  , viewWatcher:        new ViewWatcher()
  , views: {}
  };
  window.App.views.hideLinks = new HideLinks({
    el: null, model: window.App.pageWatcher});
  window.App.views.ipaKeyboardView = new IPAKeyboardView({
    el: $('#ipaKeyboard')});
  window.App.views.soundPlayOptionView = new SoundPlayOptionView({
    el: $('#topmenuSoundOptions')
  , model: window.App.soundPlayOption
  });
  window.App.views.audioLogic = new AudioLogic();
  window.App.views.mapView = new MapView({
    el: $('#contentArea')
  , model: window.App.map
  });
  window.App.views.wordlistFilter = new WordlistFilter();
  window.App.views.loadingBar = new LoadingBarView({
    el: $('.loadingBar')
  , model: window.App.loadingBar
  });
  window.App.views.parts = {
    topMenuView: new TopMenuView({
      model: window.App.templateStorage
    })
  , languageMenuView: new LanguageMenuView({
      model: window.App.templateStorage
    })
  , wordMenuView: new WordMenuView({
      model: window.App.templateStorage
    })
  , contentView: new ContentView({
      model: window.App.templateStorage
    })
  };
  window.App.views.playSequenceView = new PlaySequenceView();
  window.App.views.singleLanguageView = new SingleLanguageView();
  window.App.views.whoAreWeView = new WhoAreWeView({
    model: window.App.pageWatcher
  });
});
