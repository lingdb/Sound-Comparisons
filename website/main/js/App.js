$(function(){
  //Building the App singleton:
  window.App = {
    dataStorage:        new DataStorage()
  , downloadOptions:    new DownloadOptions()
  , linkInterceptor:    new LinkInterceptor()
  , loadingBar:         new LoadingBar()
  , logger:             new Logger()
  , map:                new Map()
  , pageWatcher:        new PageWatcher()
  , studyWatcher:       new StudyWatcher()
  , study:              new Study()
  , soundPlayOption:    new SoundPlayOption()
  , templateStorage:    new TemplateStorage()
  , translationStorage: new TranslationStorage()
  , viewWatcher:        new ViewWatcher()
  , views: {}
  };
  //Listening between models:
  window.App.studyWatcher.on('change:study', window.App.dataStorage.loadStudy, window.App.dataStorage);
  window.App.dataStorage.on('change:study', window.App.study.update, window.App.study);
  //Necessary calls to complete setup
  window.App.study.update();
  //Creating views:
  window.App.views.downloadOptionView = new DownloadOptionView({
    el: $('body'), model: window.App.downloadOptions
  });
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
  , headView: new HeadView({
      el: $('head')
    , model: window.App.templateStorage
    })
  };
  window.App.views.playSequenceView = new PlaySequenceView();
  window.App.views.singleLanguageView = new SingleLanguageView();
  window.App.views.whoAreWeView = new WhoAreWeView({
    model: window.App.pageWatcher
  });
});
