"use strict";
var StudyWatcher = Backbone.Model.extend({
  defaults: {study: null, lastStudy: null}
, initialize: function(){
    var l  = localStorage.lastStudy
      , s  = (l) ? l : 'Germanic'
      , ls = s;
    //The current study will become the last study:
    localStorage.lastStudy = s;
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
      , ls = localStorage.lastStudy || s;
    //The current study will become the last study:
    localStorage.lastStudy = s;
    //Setting the vals:
    this.set({study: s, lastStudy: ls});
  }
, studyChanged: function(){
    return this.get('study') !== this.get('lastStudy');
  }
});
