$(function(){
  //Building the App singleton:
  window.App = {
    studyWatcher:    new StudyWatcher()
  , soundPlayOption: new SoundPlayOption()
  , viewWatcher:     new ViewWatcher()
  , views: {}
  };
  window.App.views.soundPlayOptionView = new SoundPlayOptionView({
    el:    $('#topmenuSoundOptions')
  , model: window.App.soundPlayOption
  });
  window.App.views.audioLogic = new AudioLogic();
});
