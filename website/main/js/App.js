$(function(){
  //Building the App singleton:
  window.App = {
    studyWatcher:    new StudyWatcher()
  , soundPlayOption: new SoundPlayOption()
  , viewWatcher:     new ViewWatcher()
  , views: {}
  };
  if(typeof(Map) !== 'undefined'){
    window.App.map = new Map();
  }
  window.App.views.hideLinks = new HideLinks({
    el: null, model: window.App.viewWatcher});
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
});
