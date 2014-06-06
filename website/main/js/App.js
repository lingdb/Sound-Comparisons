$(function(){
  //Building the App singleton:
  window.App = {
    linkInterceptor: new LinkInterceptor()
  , studyWatcher:    new StudyWatcher()
  , soundPlayOption: new SoundPlayOption()
  , templateStorage: new TemplateStorage()
  , viewWatcher:     new ViewWatcher()
  , views: {}
  };
  if(typeof(Map) !== 'undefined'){
    window.App.map = new Map();
  }
  window.App.views.hideLinks = new HideLinks({
    el: null, model: window.App.viewWatcher});
  window.App.views.ipaKeyboardView = new IPAKeyboardView({
    el: $('#ipaKeyboard')});
  window.App.views.soundPlayOptionView = new SoundPlayOptionView({
    el:    $('#topmenuSoundOptions')
  , model: window.App.soundPlayOption
  });
  window.App.views.audioLogic = new AudioLogic();
  if(typeof(MapView) !== 'undefined'){
    window.App.views.mapView = new MapView({
      el: $('#contentArea')
    , model: window.App.map
    });
  }
  window.App.views.wordlistFilter = new WordlistFilter();
  window.App.views.loadingBar = new LoadingBar({el: $('.loadingBar')});
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
});
