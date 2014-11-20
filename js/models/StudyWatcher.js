"use strict";
var StudyWatcher = Backbone.Model.extend({
  defaults: {study: null, lastStudy: null}
, initialize: function(){
    var l  = App.storage.lastStudy
      , s  = (l) ? l : 'Germanic'
      , ls = s;
    //Check if a study is given in the fragment:
    var fragment = window.location.hash
      , matches  = fragment.match(/#\/([^\/]+)\/(map|word|language|languagesXwords|wordsXlanguages)/);
    if(_.isArray(matches)){
      s = matches[1];
    }
    //The current study will become the last study:
    App.storage.lastStudy = s;
    //Setting the vals:
    this.set({study: s, lastStudy: ls});
  }
, listen: function(){
    //We only perform once setup is complete:
    App.setupBar.onFinish(function(){
      App.study.on('change', this.update, this);
      this.update();
    }, this);
  }
  /**
    Called on complete setup and everytime the study changes.
  */
, update: function(){
    console.log('StudyWatcher.update()');
    var s  = App.study.getId() || 'Germanic'
      , ls = App.storage.lastStudy || s;
    //The current study will become the last study:
    App.storage.lastStudy = s;
    //Setting the vals:
    this.set({study: s, lastStudy: ls});
  }
, studyChanged: function(){
    return this.get('study') !== this.get('lastStudy');
  }
});
